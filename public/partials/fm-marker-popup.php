<a href="<?= get_the_permalink() ?>" class="infotitle" target="_blank"><?= $data['location_name'] ?></a>
<div class="address">
  <p class="street-address-1"><?= $data['street_address_1'] ?></p>
  <p class="street-address-2"><?= $data['street_address_2'] ?></p>
  <p class="city-state-zip"><?= $data['city'] ?> <?= $data['state'] ?> <?= $data['postal_code'] ?></p>
</div>
<div class="contact-info">
  <a class="phone" href="tel:<?= preg_replace('/[^0-9]/', '', $data['phone']) ?>"><?= $data['phone'] ?></a>
  <a href="<?= get_the_permalink() ?>" class="btn-location" target="_blank">View Location</a>
</div>
