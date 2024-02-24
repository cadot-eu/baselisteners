<?php

namespace App\EventListener\base;

use App\Service\base\BackupHelper;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;

class BackupListener
{
    private $backupDir;
    private $maxBackups;
    private $databasePath;

    public function __construct()
    {
        $this->backupDir = '/app/var/backups';
        $this->maxBackups = 1000;
        $urlParts = explode('%', $_ENV['DATABASE_URL']);
        $this->databasePath = '/app' . end($urlParts);
    }

    public function preFlush(PreFlushEventArgs $args)
    {

        $backupFilename = 'backup_' . date('Y-m-d_H-i-s') . '.sqlite';
        $filesystem = new Filesystem();
        $filesystem->copy($this->databasePath, $this->backupDir . '/' . $backupFilename, true);
    }

    public function postFlush(PostFlushEventArgs $args)
    {
        // Après le flush, nettoyez les anciennes sauvegardes si nécessaire
        $filesystem = new Filesystem();
        $backups = glob($this->backupDir . '/*.sqlite');

        if (count($backups) > $this->maxBackups) {
            $filesystem->remove(array_shift($backups));
        }
    }

    public function restoreBackup(string $backupFilename)
    {

        $filesystem = new Filesystem();
        dump($this->databasePath);
        dump($this->backupDir . '/exdata.db');
        $filesystem->copy($this->databasePath, $this->backupDir . '/exdata.db', true);
        $filesystem->copy($this->backupDir . '/' . $backupFilename, $this->databasePath, true);
    }
}
