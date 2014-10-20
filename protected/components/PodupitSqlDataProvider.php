<?php

class PodupitSqlDataProvider extends CSqlDataProvider
{
    public $prviSelect;
   
    protected function fetchData()
    {
            $sql=$this->sql;
            $db=$this->db===null ? Yii::app()->db : $this->db;
            $db->active=true;

            if(($sort=$this->getSort())!==false)
            {
                    $order=$sort->getOrderBy();
                    if(!empty($order))
                    {
                            if(preg_match('/\s+order\s+by\s+[\w\s,]+$/i',$sql))
                                    $sql.=', '.$order;
                            else
                                    $sql.=' ORDER BY '.$order;
                    }
            }

            if(($pagination=$this->getPagination())!==false)
            {
                    $pagination->setItemCount($this->getTotalItemCount());
                    $limit=$pagination->getLimit();
                    $offset=$pagination->getOffset();
                    $sql=$db->getCommandBuilder()->applyLimit($sql,$limit,$offset);
            }

            $command=$db->createCommand($this->prviSelect.'('.$sql .') AS __psdprovider_');
            foreach($this->params as $name=>$value)
                    $command->bindValue($name,$value);

            return $command->queryAll();
    }
}

?>
