<?php 
  $link = get_the_permalink();
?>
<div class="location-row location-row-<?= $i ?>">
  <a href="<?= $link ?>"><?= $data['location_name'] ?></a>

  <p><?= $data['street_address_1'] ?></p>
  <p><?= $data['street_address_2'] ?></p>
  <p><?= $data['city'] ?>, <?= $data['state'] ?> <?= $data['zip'] ?></p>  
  <p><a href="tel:<?= preg_replace('/[^0-9]/', '', $data['phone']) ?>"><?= $data['phone'] ?></a></p>

  <a href="<?= $link ?>" target="_blank">View Location</a>

</div>