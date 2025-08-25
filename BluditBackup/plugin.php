<?php

/* ONEROM */

class BluditBackup extends Plugin
{
    // --- i18n helper (PHP-based dictionaries) ---
    private $i18n = null;

    private function loadLang()
    {
        if ($this->i18n !== null) { return; }
        global $site;
        $code = 'en';
        try {
            if (isset($site) && method_exists($site, 'language')) {
                $lc = $site->language();
                if (is_string($lc) && $lc !== '') { $code = substr($lc, 0, 2); }
            }
        } catch (Exception $e) { /* fallback to en */ }
        $path = $this->phpPath() . 'PHP/lang/' . $code . '.php';
        if (!is_file($path)) { $path = $this->phpPath() . 'PHP/lang/en.php'; }
        $map = @include $path;
        if (!is_array($map)) { $map = []; }
        $this->i18n = $map;
    }

    public function t($key)
    {
        $this->loadLang();
        if (isset($this->i18n[$key])) { return $this->i18n[$key]; }
        return $key;
    }
    // --- end i18n helper ---

    // Controller für die Plugin-Verwaltung im Adminbereich
    public function adminController()
    {
        // Backup-Datei löschen
        if (isset($_GET['deletezip'])) {
            $fileToDelete = basename($_GET['deletezip']); // Sicherheitsmaßnahme
            $pathToDelete = PATH_CONTENT . 'BluditBackup/' . $fileToDelete;

            if (file_exists($pathToDelete)) {
                unlink($pathToDelete);
                Alert::set("Backup gelöscht: $fileToDelete");
            } else {
                Alert::set("Datei nicht gefunden.");
            }

            header("Location: " . DOMAIN_ADMIN . "plugin/bluditbackup/");
            exit;
        }

        // Backup erstellen
        if (isset($_POST['makebackup'])) {

            $backupDir = PATH_CONTENT . 'BluditBackup/';

            // Sicherstellen, dass das Backup-Verzeichnis existiert
            if (!file_exists($backupDir)) {
                mkdir($backupDir, 0755, true);
                file_put_contents($backupDir . '.htaccess', "Allow from all");
            }

            $folderPath = '';
            $backupType = $_POST['zip'] ?? '';

            // Zielverzeichnis für das Backup festlegen
            switch ($backupType) {
                case 'all':
                    $folderPath = PATH_ROOT;
                    break;
                case 'themes':
                    $folderPath = PATH_THEMES;
                    break;
                case 'plugins':
                    $folderPath = PATH_PLUGINS;
                    break;
                case 'pages':
                    $folderPath = PATH_PAGES;
                    break;
                case 'database':
                    $folderPath = PATH_DATABASES;
                    break;
                case 'plugins-database':
                    $folderPath = PLUGINS_DATABASES;
                    break;
                case 'uploads':
                    $folderPath = PATH_UPLOADS;
                    break;
                case 'bl-content':
                    $folderPath = PATH_CONTENT;
                    break;
                default:
                    Alert::set('Ungültige Auswahl für Backup.');
                    return;
            }

            // Dateiname für das ZIP-Archiv
            $timestamp = date('Ymd_His');
            $zipFileName = $backupDir . $timestamp . '-bludit-backup-' . $backupType . '.zip';

            
            

            $zip = new ZipArchive();

            if ($zip->open($zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {

                $files = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($folderPath, RecursiveDirectoryIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::SELF_FIRST
                );

                foreach ($files as $file) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($folderPath));

                    // Backup-Verzeichnis selbst ausschließen
                    if (strpos($filePath, $backupDir) === 0) {
                        continue;
                    }

                    if (is_dir($filePath)) {
                        $zip->addEmptyDir($relativePath);
                    } elseif (is_file($filePath)) {
                        $zip->addFile($filePath, $relativePath);
                    }
                }

                $zip->close();
                Alert::set('Backup erfolgreich erstellt: ' . basename($zipFileName));
            } else {
                Alert::set('Fehler beim Erstellen des Archivs.');
            }
        }
    }

    // Anzeige im Adminbereich
    public function adminView()
    {
        global $security;

        $tokenCSRF = $security->getTokenCSRF();
        $this->loadLang();
        $plugin = $this; // für form.php bereitstellen
        
    include($this->phpPath() . 'PHP/form.php');
}

    // Sidebar-Link im Adminbereich
    public function adminSidebar()
    {
        $pluginName = Text::lowercase(__CLASS__);
        $url = HTML_PATH_ADMIN_ROOT . 'plugin/' . $pluginName;
        $html = '<a class="nav-link" href="' . $url . '">' . $this->t('title') . '</a>';
        return $html;
    }
}
