<!-- ONEROM -->
<h3><?php echo $plugin->t('title'); ?></h3>
<p class="text-muted"><?php echo $plugin->t('subtitle'); ?></p>
<hr style="border: 0; height: 2px; background-image: linear-gradient(to right, rgba(0, 0, 0, 0), rgba(0, 0, 0, 0.15), rgba(0, 0, 0, 0));" />

<!-- Backup-Erstellung -->
<form method="POST">
    <input type="hidden" name="tokenCSRF" value="<?php echo $tokenCSRF; ?>">

    <select name="zip" class="form-control my-3">
        <option value="all"><?php echo $plugin->t('entire-website'); ?></option>
        <option value="themes"><?php echo $plugin->t('themes'); ?></option>
        <option value="plugins"><?php echo $plugin->t('plugins'); ?></option>
        <option value="pages"><?php echo $plugin->t('pages'); ?></option>
        <option value="database"><?php echo $plugin->t('databases'); ?></option>
        <option value="plugins-database"><?php echo $plugin->t('plugin-databases'); ?></option>
        <option value="uploads"><?php echo $plugin->t('uploads'); ?></option>
        <option value="bl-content"><?php echo $plugin->t('bl-content'); ?></option>
    </select>

    <button type="submit" name="makebackup" class="btn btn-primary">
        <?php echo $plugin->t('create-backup'); ?>
    </button>
</form>

<hr style="border: 0; height: 2px; background-image: linear-gradient(to right, rgba(0, 0, 0, 0), rgba(0, 0, 0, 0.15), rgba(0, 0, 0, 0));" />

<!-- Backup-Dateien anzeigen -->
<h3 class="pb-2 mt-4"><?php echo $plugin->t('existing-backups'); ?></h3>

<?php
// Backupdateien einlesen
$backupFiles = glob(PATH_CONTENT . 'BluditBackup/*.zip');
if (!is_array($backupFiles)) {
    $backupFiles = [];
}

// Sortierung nach Änderungsdatum (Default: newest)
$sort = (isset($_GET['sort']) && $_GET['sort'] === 'oldest') ? 'oldest' : 'newest';

// Neueste zuerst
usort($backupFiles, function($a, $b) {
    $ma = @filemtime($a);
    $mb = @filemtime($b);
    if ($ma === false) { $ma = 0; }
    if ($mb === false) { $mb = 0; }
    return $mb <=> $ma;
});

if ($sort === 'oldest') {
    $backupFiles = array_reverse($backupFiles);
}
?>

<ul class="list-group">
<?php
if (empty($backupFiles)) {
    echo '<li class="list-group-item text-muted">' . $plugin->t('no-backups') . '</li>';
} else {
    foreach ($backupFiles as $zip) {
        $filename = pathinfo($zip, PATHINFO_BASENAME);
        $filenameEscaped = htmlentities($filename, ENT_QUOTES, 'UTF-8');
        $fileUrl = HTML_PATH_CONTENT . 'BluditBackup/' . $filenameEscaped;
        $deleteUrl = DOMAIN_ADMIN . 'plugin/bluditbackup?deletezip=' . urlencode($filename);

        // Bestätigungstext mit Dateinamen
        $msg = sprintf($plugin->t('confirm-delete-backup'), $filenameEscaped);

        echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
        echo '<a href="' . $fileUrl . '" download>' . $filenameEscaped . '</a>';

        // onclick mit einfachen Quotes außen
        echo '<a class="btn btn-danger btn-sm" href="' . $deleteUrl . '" onclick=\'return confirm(' . json_encode($msg) . ');\'>'
            . $plugin->t('delete')
            . '</a>';

        echo '</li>';
    }
}
?>
</ul>

<!-- Sortierung auswählen -->
<form method="GET" class="form-inline mb-3 mt-3">
    <label for="sort" class="mr-2"><?php echo $plugin->t('sort-label'); ?></label>
    <select name="sort" id="sort" class="form-control mr-2" onchange="this.form.submit()">
        <option value="newest" <?php echo ($sort === 'newest') ? 'selected' : ''; ?>>
            <?php echo $plugin->t('newest-first'); ?>
        </option>
        <option value="oldest" <?php echo ($sort === 'oldest') ? 'selected' : ''; ?>>
            <?php echo $plugin->t('oldest-first'); ?>
        </option>
    </select>
</form>

<hr style="border: 0; height: 2px; background-image: linear-gradient(to right, rgba(0, 0, 0, 0), rgba(0, 0, 0, 0.15), rgba(0, 0, 0, 0));" />
	<p class="copyright" style="text-align:center;">From Α ∞ Ω by multicolor and with a little help from a friend ONEROM</a></p>
<hr style="border: 0; height: 2px; background-image: linear-gradient(to right, rgba(0, 0, 0, 0), rgba(0, 0, 0, 0.15), rgba(0, 0, 0, 0));" />
