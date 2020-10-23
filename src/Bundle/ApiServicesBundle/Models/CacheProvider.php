<?php
/*
 * This file is part of the cobhimself/api-services-bundle package.
 *
 * (c) Collin D. Brooks <collin.brooks@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cob\Bundle\ApiServicesBundle\Models;

use Doctrine\Common\Cache\PhpFileCache;
use InvalidArgumentException;

class CacheProvider extends PhpFileCache implements CacheProviderInterface
{
    private $lifetime = 0;

    /**
     * @inheritDoc
     */
    public function setLifeTime(int $lifetime): CacheProviderInterface
    {
        $this->lifetime = $lifetime;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setLifeTimeMinutes(float $minutes): CacheProviderInterface
    {
        $this->setLifeTime($minutes * 60);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setLifeTimeHours(float $hours): CacheProviderInterface
    {
        $this->setLifeTime($hours * 60 * 60);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setLifeTimeDays(float $days): CacheProviderInterface
    {
        $this->setLifeTime($days * 24 * 60 * 60);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getLifetime(): int
    {
        return $this->lifetime;
    }

    /**
     * @inheritDoc
     */
    public function clearLifetime(): CacheProviderInterface
    {
        $this->setLifeTime(0);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function save($id, $data, $lifeTime = 0): bool
    {
        $this->warnIfLifeTimeSent($lifeTime);

        return parent::save($id, $data, $this->getLifetime());
    }

    /**
     * @inheritDoc
     */
    public function saveMultiple(array $keysAndValues, $lifetime = 0): bool
    {
        $this->warnIfLifeTimeSent($lifetime);

        return parent::saveMultiple($keysAndValues, $this->getLifetime());
    }

    /**
     * @inheritDoc
     */
    public function getStatsLines(): array
    {
        $cacheStats = $this->getStats();
        $usage      = $cacheStats[self::STATS_MEMORY_USAGE];
        $available  = $cacheStats[self::STATS_MEMORY_AVAILABLE];

        //Format our data
        $usage     = $this->formatBytes($usage, 0);
        $available = $this->formatBytes($available, 0);

        return [sprintf(
            'Cache usage: %s (%s available)',
            $usage,
            $available
        )];
    }

    /**
     * Warn if the lifetime for a cache save is sent in since we are overriding
     * this behavior.
     *
     * @param int $lifeTime
     */
    private function warnIfLifeTimeSent(int $lifeTime)
    {
        if ($lifeTime !== 0) {
            throw new InvalidArgumentException(sprintf('Lifetime should not be set directly in the cache provider methods! Use %s instead!', self::class . '::setLifeTime()'));
        }
    }

    private function formatBytes($bytes, $precision): string
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . $units[$pow];
    }
}
