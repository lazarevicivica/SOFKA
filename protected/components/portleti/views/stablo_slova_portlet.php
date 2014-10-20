<div class="portlet dropshadow">
    <div class="portlet-title">
        <?php echo $this->title?>
    </div>
    <div id="lista-zbirki" class="portlet-content min-visina">
        <ul>
    <?php
        echo Zbirkaslova::getListaOtvorenih($otvorenaKategorija, new StabloVisitor());
    ?>
        </ul>
    </div>
</div>