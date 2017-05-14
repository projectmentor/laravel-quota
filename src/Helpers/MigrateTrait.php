<?php

/**
 * This file is part of laravel-quota
 *
 * (c) David Faith <david@projectmentor.org>
 *
 * Full copyright and license information is available
 * in the LICENSE file distributed with this source code.
 */

namespace Projectmentor\Quota\Helpers;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Filesystem\ClassFinder;

/**
 * This is the migrate trait.
 *
 * @author David Faith <david@projectmentor.org>
 */
trait MigrateTrait
{

    /**
     * Path to migration files to run.
     *
     * @var string
     */
    protected $migrations_path;


    /**
     * Migrate All Files.
     *
     * @after
     *
     * @return void
     */
    public function migrate($path=null, $files=[])
    {
        //default migration path;
        $this->migrations_path = base_path('database/migrations');
        $this->migrations_path = $path ? $path : $this->migrations_path;

        $fileSystem = app(Filesystem::class);
        $classFinder = app(ClassFinder::class);

        if(!empty($files)){
            //run specific files

            foreach($files as $file)
            {
                $file = $this->migrations_path . "/" . $file;

                $fileSystem->requireOnce($file);
                $migrationClass = $classFinder->findClass($file);
                (new $migrationClass)->up();
            }

        }else{

            \Log::info($fileSystem->files($this->migrations_path));

            foreach($fileSystem->files($this->migrations_path) as $file)
            {
                $fileSystem->requireOnce($file);
//                var_dump($file);
                $migrationClass = $classFinder->findClass($file);
                (new $migrationClass)->up();
            }
        }
    }


}
