<?php if(!empty($height)) : ?>
  <style type="text/css">
    #<?= $unique_id ?> {
      height: <?= $height ?>;
    }
  </style>
<?php endif; ?>

<form action="" action="GET">
  <input type="text" class="fm-autocomplete" name="fm-search" value="" autocomplete="off">
  <button type="submit">Submit</button>
</form>

<div class="fm-map-container">
  <div id="<?= $unique_id ?>" data-map="<?= $mapId ?>" class="fm-google-map flex-map-<?= $mapId ?>" data-load-type="<?= $load_type ?>" <?php foreach ($attributes as $key => $attr) { echo " data-{$key}='".html_entity_decode($attr)."' "; } ?>>
    <span class="fm-spinner"></span>
  </div>
</div>