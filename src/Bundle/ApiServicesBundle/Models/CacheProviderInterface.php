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

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\ClearableCache;
use Doctrine\Common\Cache\FlushableCache;
use Doctrine\Common\Cache\MultiGetCache;
use Doctrine\Common\Cache\MultiPutCache;

interface CacheProviderInterface extends Cache, FlushableCache, ClearableCache, MultiGetCache, MultiPutCache
{
    /**
     * Set the lifetime, in seconds, for the cache to be saved.
     *
     * @param int $lifetime
     */
    public function setLifeTime(int $lifetime): CacheProviderInterface;

    /**
     * Set the lifetime, in minutes, for the cache to be saved.
     *
     * @param float $minutes
     */
    public function setLifeTimeMinutes(float $minutes): CacheProviderInterface;

    /**
     * Set the lifetime, in hours, for the cache to be saved.
     *
     * @param float $hours
     */
    public function setLifeTimeHours(float $hours): CacheProviderInterface;

    /**
     * Set the lifetime, in days, for the cache to be saved.
     *
     * @param float $days
     */
    public function setLifeTimeDays(float $days): CacheProviderInterface;

    /**
     * Get the lifetime set for the cache in seconds.
     *
     * @return int
     */
    public function getLifetime(): int;

    /**
     * Clear the lifetime value for the cache so it lasts forever.
     */
    public function clearLifetime(): CacheProviderInterface;

    /**
     * Return an array of lines with statistics for this cache provider.
     *
     * @return array
     */
    public function getStatsLines(): array;
}