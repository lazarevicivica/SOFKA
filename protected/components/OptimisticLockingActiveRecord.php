<?php
/**
  * OptimisticLockingActiveRecord class file.
  *
  * @author seb
  * @package app.models
  */

 /**
  * OptimisticLockingActiveRecord will automatically fill object version and do not
  * allow.to update / delete record if data has been changed by another user
  *
  * Usage:
  * 1. Add locking_version field to the database table
  * 2. Inherit model class from OptimisticLockingActiveRecord
  * 3. Add 'lock_version' hidden field to the edit form
  * 4. Handle StaleObjectError exception when saving record, for example
  *    try {
  *        $result = $model->save();
  *     } catch (StaleObjectError $e) {
  *        $model->addError('lock_version', $e->getMessage());
  *        return false;
  *     }
  *
  *
  * @author seb
  * @package app.models
  */
class OptimisticLockingActiveRecord extends CActiveRecord {

    /*
     * Can not implement this as behavior, because CActiveRecord::update() does not allow
     * to change criteria for SQL update
     * And optimistic locking requires this to implement safe locking
     */

    /**
        * Returns the name of the attribute to store object version number.
        * Defaults to 'lock_version'
     * @return string locking attibute name
     */
        public function getlockingAttribute() {
        return 'verzija';
    }

    /**
     * Overrides parent implementation to add object version check during update
     * @param mixed $pk primary key value(s). Use array for multiple primary keys.
     * For composite key, each key value must be an array (column name=>column value).
         * @param array $attributes list of attributes (name=>$value) to be updated
         * @param mixed $condition query condition or criteria.
         * @param array $params parameters to be bound to an SQL statement.
         * @return integer the number of rows being updated
     */
    public function updateByPk($pk,$attributes,$condition='',$params=array()) {
        $this->applyLockingCondition($condition);

        //increment object version
        $lockingAttribute = $this->getlockingAttribute();
        $attributes[$lockingAttribute] = $this->$lockingAttribute + 1;

        $affectedRows = parent::updateByPk($pk, $attributes, $condition, $params);
        if ($affectedRows != 1) {
            throw new BajatObjekat(Yii::t('biblioteka', 'Податке је изменио други корисник'));
        }
        $this->$lockingAttribute = $this->$lockingAttribute + 1;
        return $affectedRows;
        }

    /**
     * Overrides parent implementation to add object version check during delete
     * @param mixed $pk primary key value(s). Use array for multiple primary keys. For composite key, each key value must be an array (column name=>column value).
         * @param mixed $condition query condition or criteria.
         * @param array $params parameters to be bound to an SQL statement.
         * @return integer the number of rows deleted
     */
        public function deleteByPk($pk,$condition='',$params=array())
        {
                $this->applyLockingCondition($condition);
        $affectedRows = parent::deleteByPk($pk, $condition, $params);
        if ($affectedRows != 1) {
            throw new BajatObjekat(Yii::t('app','Податке је изменио други корисник'));
        }
        return $affectedRows;
        }

    /**
     * Adds check for object version to the specified condition and increments version
     * @param string $condition initial condition
     */
    private function applyLockingCondition(&$condition) {
        $lockingAttribute = $this->getlockingAttribute();
        $lockingAttributeValue = $this->$lockingAttribute;

        if (!empty($condition))
            $condition .= ' and ';
        $condition .= "$lockingAttribute = $lockingAttributeValue";
    }

}