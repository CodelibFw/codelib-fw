<?php

namespace cl\util\traits;

use cl\core\CLDependency;

/**
 * Helper trait to make simpler to inject the ActiveRepo into Plugins
 * Classes using this trait must declare this class variable:
 * private $activeRepo;
 */
trait ActiveRepoIoCHelper
{
    private $activeRepo;
    
    /**
     * @param mixed $activeRepo
     * @return CLUserPlugin
     */
    public function setActiveRepo($activeRepo)
    {
        $this->activeRepo = $activeRepo;
        return $this;
    }

    public function getActiveRepo()
    {
        return $this->activeRepo;
    }

    /**
     * @return array with required dependencies
     */
    public function dependsOn(): array
    {
        return [CLDependency::new(ACTIVE_REPO)];
    }
}
