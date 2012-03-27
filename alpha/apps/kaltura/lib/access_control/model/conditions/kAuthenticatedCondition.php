<?php
/**
 * @package Core
 * @subpackage model.data
 */
class kAuthenticatedCondition extends kCondition
{
        /* (non-PHPdoc)
         * @see kCondition::__construct()
         */
        public function __construct($not = false)
        {
                $this->setType(ConditionType::AUTHENTICATED);
                parent::__construct($not);
        }

        /**
         * The privelege needed to remove the restriction
         *
         * @var array
         */
        protected $privileges = array(ks::PRIVILEGE_VIEW, ks::PRIVILEGE_VIEW_ENTRY_OF_PLAYLIST);

        /**
         * @param array $privileges
         */
        public function setPrivileges(array $privileges)
        {
                $this->privileges = $privileges;
        }

        /**
         * @return array
         */
        function getPrivileges()
        {
                return $this->privileges;
        }

        /* (non-PHPdoc)
         * @see kCondition::internalFulfilled()
         */
        public function internalFulfilled(accessControl $accessControl)
        {
                $scope = $accessControl->getScope();
                if (!$scope->getKs() || (!$scope->getKs() instanceof ks))
                        return false;

                if ($scope->getKs()->isAdmin())
                        return true;

                KalturaLog::debug(print_r($this->privileges, true));
                foreach($this->privileges as $privilege)
                {
                        if(is_object($privilege))
                                $privilege = $privilege->getValue();

                        KalturaLog::debug("Checking: " . print_r($privilege, true));
                        KalturaLog::debug("Entry: " . $scope->getEntryId());
                        if($scope->getKs()->verifyPrivileges($privilege, $scope->getEntryId()))
                        {
                                KalturaLog::debug("OK: " . print_r($privilege, true));
                                return true;
                        }
                }

                KalturaLog::debug("returned false");
                return false;
        }
}
