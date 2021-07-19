<?= $this->extend('frontend/_partials/overview') ?>

<?= $this->section('content') ?>

<!-- hero -->
<div class="content">
  <div class="title-page">
    Contact Us
  </div>

  <!-- contact box  -->
  <div class="contact-box-wrap">
    <div class="container">
      <div class="row">
        <div class="col-md-4">
          <div class="contact-box">
            <img src="<?= base_url('adw/assets/images/map-pin.png') ?>" alt="">
            <h5>Our Headquarter</h5>
            <p>Jalan Indokarya III Blok F1-2,
              Tanjung Priok, Kota Jakarta Utara,
              DKI Jakarta 14340</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="contact-box">
            <img src="<?= base_url('adw/assets/images/headphones.png') ?>" alt="">
            <h5>Lets Talk</h5>
            <p>Phone : (021) 6583 1188</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="contact-box">
            <img src="<?= base_url('adw/assets/images/mail.png') ?>" alt="">
            <h5>Our Mail</h5>
            <p>info@sahabatabadi.com</p>
          </div>
        </div>
      </div>
    </div>
  </div>


  <!-- form contact -->
  <div class="half-half-wrap pb-0">
    <div class="container">
      <div class="row">
        <div class="col-md-4">
          <h4 class="left-title">How we can help you?</h4>
        </div>
        <div class="col-md-8 card-form">
          <form class="form-contact" id="form_contact">
            <?= csrf_field(); ?>
            <p class="desc">Fill out the form and we'll be in touch soon!</p>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <input type="text" class="form-control" placeholder="Name" name="name" id="name" autocomplete="off">
                  <small class="form-text text-danger" id="error_name"></small>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <input type="email" class="form-control" placeholder="Email address" name="email" id="email" autocomplete="off">
                  <small class="form-text text-danger" id="error_email"></small>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <input type="text" class="form-control" placeholder="Phone number" name="phone" id="phone" autocomplete="off">
                  <small class="form-text text-danger" id="error_phone"></small>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <select class="form-control" name="inquiry" id="inquiry">
                    <option value="General">General Inquiry</option>
                  </select>
                  <small class="form-text text-danger" id="error_inquiry"></small>
                </div>
              </div>
              <div class="col-md-12">
                <div class="form-group">
                  <input type="text" class="form-control" placeholder="Subject" name="subject" id="subject" autocomplete="off">
                  <small class="form-text text-danger" id="error_subject"></small>
                </div>
              </div>
              <div class="col-md-12">
                <div class="form-group">
                  <textarea class="form-control" rows="5" placeholder="Message" name="message" id="message"></textarea>
                  <small class="form-text text-danger" id="error_message"></small>
                </div>
              </div>
              <div class="col-md-12">
                <div class="custom-control custom-checkbox">
                  <input type="checkbox" class="custom-control-input" id="customCheck1">
                  <label class="custom-control-label" for="customCheck1">
                    I accept the terms & conditions and I understand that my data will be hold securely in accordance with the privacy policy.
                  </label>
                </div>
              </div>
            </div>
            <div class="clearfix"></div>
            <button type="button" class="btn btn-primary mt-3 submit_form">Send</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="container">
  <div class="row">
    <div class="col-md-12">
      <footer>
        <p>© Copyright 2021 PT Sahabat Abadi Sejahtera - All rights reserved.</p>
        <a href="javascript:void(0);" onclick="window.open('https://www.facebook.com/pages/PT.%20Sahabat%20Abadi%20Sejahtera/268648619839788/', '_blank')" class="foot-socmed"><i class="fa fa-facebook-f"></i></a>
        <a href="javascript:void(0);" onclick="window.open('https://www.youtube.com/channel/UCeB2XhHrFdFD3P1cw3Q9swg', '_blank')" class="foot-socmed"><i class="fa fa-youtube"></i></a>
        <a href="javascript:void(0);" onclick="window.open('https://www.linkedin.com/company/sahabat-abadi-sejahtera', '_blank')" class="foot-socmed"><i class="fa fa-linkedin"></i></a>
        <a href="javascript:void(0);" onclick="window.open('https://www.instagram.com/explore/locations/317814588/ptsahabat-abadi-sejahtera-philips-indonesia-sunter', '_blank')" class="foot-socmed"><i class="fa fa-instagram"></i></a>
      </footer>
    </div>
  </div>
</div>
<?= $this->endSection() ?>