<?php
/*******************************************************************************
 * NODE
 * Pomocna klasa, sadrzi niz
 ******************************************************************************/

class Node
{
    public $id;
    public $text;
    public $obj;
    public $children;

    public function __construct($obj, $txtAtribut)
    {
        $this->id = $obj->id;
        $this->text = $obj->$txtAtribut;
        $this->obj = $obj;
        $this->children = array();
    }

    public function dodajDete($node)
    {
        $this->children[] = $node;
    }

    static function cmp($a, $b)
    {        
        return Helper::cmp($a->text, $b->text);
    }
}

/*******************************************************************************
 * STABLO
 ******************************************************************************/

class Stablo 
{

    private $m_txtAtribut;
    private $m_klasa;
    public function  __construct($klasa, $nazivTxtAtributa)
    {
        $this->m_txtAtribut = $nazivTxtAtributa;
        $this->m_klasa = $klasa;

        //klasa mora sadrzati atribute
               /* integer $id
                integer $roditelj
                integer $levo
                integer $desno
                integer $id_jezik_originala*/
        //TODO doadati assert proveru !!!
/*        assert(array_key_exists('id', $klasa::model()->getAttributes()));
        assert(array_key_exists('roditelj', $klasa::model()->getAttributes()));
        assert(array_key_exists('levo', $klasa::model()->getAttributes()));
        assert(array_key_exists('desno', $klasa::model()->getAttributes()));
        assert(array_key_exists('id_jezik_originala', $klasa::model()->getAttributes()));*/
       // assert(isset($this->deca));
    }


    /**
     *
     * @param <m_klasa> $roditelj
     * @param <int> $levo
     * @return <int> vraca desnu vrednost cvora uvecanu za jedan
     */
    public function rekonstruisiStablo($roditelj, $levo=1)
    {
       $klasa = $this->m_klasa;
       // desna vrednost tekuceg cvora je leva vrednost + 1
       $desno = $levo+1;
       // sva deca tekuceg cvora
       $result = $klasa::model()->findAll('roditelj=:id', array(':id'=>$roditelj->id));
       foreach($result as $cvor)
       {
           // rekurzivno poziva funkcju za svako dete ovog cvora
           // $desno je trenutna desna vrednost koja se uvecava u fukciji rekonstruisiStablo
           $desno = $this->rekonstruisiStablo($cvor, $desno);
       }
       // vec sam imao levu vrednost a sada posto su obradjena
       // deca ovog cvora, takodje znam i desnu vrednost
       $roditelj->levo = $levo;
       $roditelj->desno = $desno;
       //var_dump($roditelj);
       if( ! $roditelj->save())
       {
            $poruka = '';
            $greske = $roditelj->getErrors();
            var_dump($greske);
            throw new Exception ('Неуспешна реконструкција стабла! ');
       }
       // vraca desnu vrednost ovog cvora, uvecanu za jedan
       return $desno+1;
    }

/**
 *
 * @param <$m_klasa> $root - Element cije se podstablo trazi
 * @return Node - objekat koji sadrzi podstablo
 *
 */
    public function getStrukturaStabla($root, $id_jezik = null)
    {
        if($id_jezik === null)
            $id_jezik = Helper::getAppjezikId ();
        $condition = new CDbCriteria();
        $condition->addBetweenCondition('levo', $root->levo, $root->desno);
        $condition->order = 'levo ASC';
        $klasa = $this->m_klasa;
        $niz = $klasa::model()->with(array('ri18n'=>array('condition'=>"id_jezik=$id_jezik")))->findAll($condition);
        if(count($niz) == 0)
            return null;
        $rootNode = new Node($niz[0], $this->m_txtAtribut);
        $curNode = $rootNode;
        $curLevo = $niz[0]->levo;
        $nodeStack = array(0 => $curNode);
        $n = count($niz);
        for($i=1; $i<$n; $i++)
        {
            $vratiSe = $niz[$i]->levo - ($curLevo + 1);
            while($vratiSe > 0)
            {
                array_pop($nodeStack);
                $vratiSe--;
            }
            assert(count($nodeStack) > 0);
            $curNode = $nodeStack[count($nodeStack)-1]; //poslednji element na steku
            assert($curNode != null);
            $dete = new Node($niz[$i], $this->m_txtAtribut);
            $curNode->dodajDete($dete);
            $curNode = $dete;
            $nodeStack[] = $curNode;
            $curLevo = $niz[$i]->levo;
        }
        return $rootNode;
    }
    
    /**
     * @param <mixed>  $root - int ili $m_klasa
     * moze biti klase $m_klasa, definisane u konstruktoru stabla
     * ili int - id objekta cija se deca traze.
     */
    public function getDeca($root, $id_jezik = null)
    {

        if( ! $id_jezik)
            $id_jezik = Helper::getAppjezikId();
        $condition = new CDbCriteria();
        $condition->compare('roditelj', '='.$root->id);
        $condition->order = 'redosled, '.$this->m_txtAtribut . ' ASC';
        $klasa = $this->m_klasa;
        return $klasa::model()->with(array('ri18n'=>array('condition'=>"id_jezik=$id_jezik")))->findAll($condition);
        //return $klasa::model()->with('ri18n')->findAll($condition);
    }

    /**
     *
     * @param <int> $idCvor  - id elementa za koji se trazi putanja
     * @return array<m_klasa> Niz elemenata koji cine putanju do trazenog
     */
    public function getPutanja($idCvor)
    {
        $idCvor = intval($idCvor);
        $klasa = $this->m_klasa;
        $cvor = $klasa::model()->findByPk($idCvor);
        if(!$cvor)                    
            return array();        
        $condition = new CDbCriteria();
        $condition->compare('t.levo', '<='.$cvor->levo);
        $condition->compare('t.desno', '>='.$cvor->desno);
        $condition->order = 't.levo ASC';
        $klasa = $this->m_klasa;
        return $klasa::model()->findAll($condition);
    }

    /**     
     *  
     */
    public function getListaOtvorenihAdjency($otvoren, $visitor)//getOtvorenaStrukturaStabla($idOtvoren)
    {        
        $putanja = $this->getPutanja($otvoren->id);
        if( ! $putanja)
            return false;        
        $root = $putanja[0];        
        //echo 'root'. $root->id;
        assert($root->id == 1);
        $this->posetiListuOtvorenihAdjency($root, $visitor, 0, $putanja, $otvoren);
    }    
    
    private function posetiListuOtvorenihAdjency($node, $visitor, $nivo, array &$putanja, $otvoren)
    {
        $deca = $this->getDeca($node);
        foreach($deca as $n)
        {
            $selektovan = $n->id == $otvoren->id;
            $visitor->visit($n, $nivo, $selektovan);
            if(isset($putanja[$nivo+1]) && $n->id == $putanja[$nivo+1]->id)
                $this->posetiListuOtvorenihAdjency($n, $visitor, $nivo+1, $putanja, $otvoren);
        }
    }
    
    public function getListaOtvorenih($otvoren, $visitor)
    {
        $putanja = $this->getPutanja($otvoren->id);        
        if( ! $putanja)
            return false;        
        $node = $this->getStrukturaStabla($this->root());
        $this->posetiListuOtvorenih($node, $visitor, 0, $putanja, $otvoren);
    }

    private function posetiListuOtvorenih($node, $visitor, $nivo, array &$putanja, $otvoren)
    {      
        usort(  $node->children, array('Node', 'cmp'));
        foreach($node->children as $n)
        {
            $selektovan = $n->id == $otvoren->id;
            $visitor->visit($n->obj, $nivo, $selektovan);
            if(isset($putanja[$nivo+1]) && $n->id == $putanja[$nivo+1]->id)
                $this->posetiListuOtvorenih($n,$visitor, $nivo+1, $putanja, $otvoren);
        }
    }
    
    public function root()
    {
        $klasa = $this->m_klasa;
        return $klasa::model()->findByPk(1);
    }

    /**     
     * @param <string> $idCvor - oblika je id_<int>
     * @return string  vraca string nalik stringu: "id_1","id_2","id_3"
     */
    public function getIdPutanju($idCvor)
    {
        $idar = explode('_',$idCvor);
        $idCvor = intval($idar[1]);        
        $izlaz = '';
        $niz = $this->getPutanja($idCvor);
        foreach($niz as $p)
            $izlaz .= '"id_'.$p->id.'",';
        $izlaz .= '"id_'.$cvor->id.'"';
        return $izlaz;
    }

    /**
     *
     * @param <$m_klasa> $root definise podstablo
     * @return <string> vraca stablo u obliku ul liste
     */
    public function getULStablo($root)
    {
        $rootNode = $this->getStrukturaStabla($root);
        if(!$rootNode)
            $ul = '<ul></ul>';
        else
        {
            $ul = '';
            $ul.=$this->getOtvoreniLI($rootNode);
            $ul .= '<ul>';
            $this->generisiUL($rootNode, $ul);
            $ul.='</ul></li>'; //zatvarajuci tagovi za root element
        }
        return '<ul>'.$ul.'</ul>';
    }

    private function getOtvoreniLI($rootNode)
    {
        return '<li id="id_'.$rootNode->id.'" title="'.$rootNode->text.'"> <a href="#">'.$rootNode->text.'</a>';
    }

    private function generisiUL($node, &$ul, $isRoot=true)
    {    
        
        usort(  $node->children, array('Node', 'cmp'));
        foreach($node->children as $dete)
        {
            $ul .= $this->getOtvoreniLI($dete);
            if(count($dete->children))
            {
                $ul.='<ul>';
                $this->generisiUL($dete, $ul, false);
                $ul.='</ul>';
            }
            $ul.='</li>';
        }
    }

}