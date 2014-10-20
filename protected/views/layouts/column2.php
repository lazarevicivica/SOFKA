<?php $this->beginContent('//layouts/main'); ?>




<div class="container">

<?php if(Yii::app()->controller->getId() == 'site' && $this->action->Id == 'index'):?>
<?php
    $request = Yii::app()->request;
    $cookie = $request->cookies['sakrij_okvir'];
    
    if( ! $cookie || ! $cookie->value):
?>
       <div class="okvir">
           <div class="left">
                <?php $this->widget('SliderPortlet');?>
           </div>
           <div class="podaci-naslovna">
               <div class="naslovna-kontakt-naslov"><?php echo Yii::t('biblioteka', 'Адреса:');?></div>
               <div class="naslovna-kontakt-podaci">
                    <?php echo Yii::t('biblioteka', 'Народна библиотека<br/> "Радислав Никчевић"<br/>Кнегиње Милице 2-4<br/> 35000 Јагодина<br/>Србија');?>
               </div>
               
               <div class="naslovna-kontakt-naslov"><?php echo Yii::t('biblioteka', 'Електронска пошта:');?></div>
               <div class="naslovna-kontakt-podaci">
                    nbjag@ptt.rs
               </div>
               
               <div class="naslovna-kontakt-naslov"><?php echo Yii::t('biblioteka', 'Телефони:');?></div>
               <div class="naslovna-kontakt-podaci">
                    (+381)035/244-580<br/>
                    (+381)035/221-817
               </div>
               
               <div class="naslovna-kontakt-naslov"><?php echo Yii::t('biblioteka', 'Радно време:');?></div>
               <div class="naslovna-kontakt-podaci">
                    8-19 <?php echo Yii::t('biblioteka', 'часова радним даном');?><br/>
                    8-13 <?php echo Yii::t('biblioteka', 'часова суботом');?>
               </div>
           </div>            
       </div>
    <div id="dugme-sakrij-okvir">
                <?php
                    $parametri = array('sakrij_animaciju'=>1);
                    if(isset($_GET['page']))
                        $parametri['page'] = $_GET['page'];
                    $url = Helper::createI18nUrl('site/index', null, $parametri);
                ?>
                <a href ="<?php echo $url;?>">
                    <img src="/images/sajt/strelica-gore.png" title="<?php echo Yii::t('biblioteka','Сакриј');?>" alt=""/>
                </a>
            </div>
    <?php else:?>
                <div id="dugme-prikazi-okvir">
                    <?php
                        $parametri = array('sakrij_animaciju'=>0);
                        if(isset($_GET['page']))
                            $parametri['page'] = $_GET['page'];
                        $url = Helper::createI18nUrl('site/index', null, $parametri);
                    ?>
                    <a href ="<?php echo $url;?>">
                        <img src="/images/sajt/strelica-dole.png" title="<?php echo Yii::t('biblioteka','Прикажи');?>" alt=""/>
                    </a>
                </div>
    <?php endif;?>
<?php endif;?>

	 <div class="span-17">         
            <div id="content">
                    <?php echo $content; ?>
            </div><!-- content -->
	</div>
	<div class="span-7 last">         
            <div id="sidebar">
                <?php $baneriVrh = ! in_array(Yii::app()->controller->getId(), array('digital', 'knjiga'), true);?>
                <?php if($baneriVrh):?>
                    <div id="grad-buducnosti">
                        <a title="Град Јагодина - град будућности" href="http://www.jagodina.org.rs/"><img  class="dropshadow" style="margin-bottom: 15px;" alt="Jagodina grad budućnosti" src ="<?php echo Helper::baseUrl('images/sajt/grad-buducnosti.png') ?>"/></a>
                        <a title="Skola stranih jezika" href="http://www.akademijaoxford.com"><img  class="dropshadow" style="margin-bottom: 15px;" alt="Skola stranih jezika" src ="<?php echo Helper::baseUrl('images/sajt/oxford.png') ?>"/></a>                        
                    </div>
                <?php endif;?>
                
		<?php $this->prikaziPortlete();?>
                
            </div>
	</div>
        <div class="clear"></div>
</div>
<?php $this->endContent(); ?>