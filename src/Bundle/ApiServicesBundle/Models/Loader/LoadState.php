<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Loader;

class LoadState
{
    const WAITING = 1;
    const LOADED = 2;
    const LOADED_WITH_DATA = 3;

    /**
     * @var int the state of this load state
     */
    private $state;

    private function __construct(int $state)
    {
        $this->state = $state;
    }

    public static function waiting(): LoadState
    {
        return new LoadState(self::WAITING);
    }

    public static function loaded(): LoadState
    {
        return new LoadState(self::LOADED);
    }

    public static function loadedWithData(): LoadState
    {
        return new LoadState(self::LOADED_WITH_DATA);
    }

    public function isLoaded()
    {
        return $this->isState(self::LOADED);
    }

    public function isLoadedWithData()
    {
        return $this->isState(self::LOADED_WITH_DATA);
    }

    public function isWaiting()
    {
        return $this->isState(self::WAITING);
    }

    private function isState($state)
    {
        return $this->getState() === $state;
    }

    /**
     * @return int
     */
    private function getState(): int
    {
        return $this->state;
    }

}