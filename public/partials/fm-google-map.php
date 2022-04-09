<?php if(!empty($height)) : ?>
  <style type="text/css">
    #<?= $unique_id ?> {
      height: <?= $height ?>;
    }
  </style>
<?php endif; ?>

<?php do_action('fm_start_map', $id, $unique_id); ?>
<div class="fm-map-container">
  <div class="fm-spinner-container">
    <span class="fm-spinner"><div></div><div></div></span>
  </div>
  <?php do_action('fm_before_map', $id, $unique_id); ?>
  <div id="<?= $unique_id ?>" data-map="<?= $mapId ?>" class="fm-google-map flex-map-<?= $mapId ?>" data-load-type="<?= $load_type ?>" <?php foreach ($attributes as $key => $attr) { echo " data-{$key}='".html_entity_decode($attr)."' "; } ?>>
    <?php do_action('fm_inside_map', $id, $unique_id); ?>
  </div>
  <?php do_action('fm_after_map', $id, $unique_id); ?>
</div>
<?php do_action('fm_end_map', $id, $unique_id); ?>