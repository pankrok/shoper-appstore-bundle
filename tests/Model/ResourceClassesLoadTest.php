<?php

namespace PanKrok\ShoperAppstoreBundle\Tests\Model;

use PanKrok\ShoperAppstoreBundle\Model\BulkModel;
use PanKrok\ShoperAppstoreBundle\Model\ResourceModel;
use PHPUnit\Framework\TestCase;

/**
 * Guards against property-type mismatches between RequestModel and its
 * subclasses (an untyped "$url" in a resource is a fatal error at class load).
 */
final class ResourceClassesLoadTest extends TestCase
{
    public function testEveryResourceClassLoads(): void
    {
        $dir   = \dirname(__DIR__, 2) . '/src/Model/Resource';
        $files = glob($dir . '/*.php');

        self::assertNotEmpty($files);

        foreach ($files as $file) {
            $class = 'PanKrok\\ShoperAppstoreBundle\\Model\\Resource\\' . basename($file, '.php');

            self::assertTrue(class_exists($class), $class . ' failed to load');
            self::assertTrue(is_subclass_of($class, ResourceModel::class), $class . ' must extend ResourceModel');
        }
    }

    public function testBulkModelLoads(): void
    {
        self::assertTrue(class_exists(BulkModel::class));
    }
}
