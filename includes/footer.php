<footer>
  <div class="footer-inner">
    <div class="footer-brand">
      <div class="logo-text">Noor <span>Pharmacy</span></div>
      <p>Your trusted healthcare partner since 2010. Providing authentic medicines and expert care across Dhaka.</p>
    </div>
    <div class="footer-col">
      <h4>Quick Links</h4>
      <a href="<?= str_repeat('../', $depth ?? 0) ?>index.php">Home</a>
      <a href="<?= str_repeat('../', $depth ?? 0) ?>medicines.php">Medicines</a>
      <a href="<?= str_repeat('../', $depth ?? 0) ?>register.php">Register</a>
      <a href="<?= str_repeat('../', $depth ?? 0) ?>login.php">Login</a>
    </div>
    <div class="footer-col">
      <h4>Contact</h4>
      <p>📍 123 Health Street, Dhaka 1000</p>
      <p>📞 +880 1700-000000</p>
      <p>✉️ info@noorpharmacy.com</p>
    </div>
    <div class="footer-col">
      <h4>Opening Hours</h4>
      <p>Sat–Thu: 8AM – 10PM</p>
      <p>Friday: 2PM – 10PM</p>
      <p style="color:var(--primary);font-weight:700;margin-top:8px">🟢 Open Now</p>
    </div>
  </div>
  <div class="footer-bottom">
    <p>© <?= date('Y') ?> <span>Noor Pharmacy</span>. All rights reserved.</p>
    <p>Group 7 | CSE 3105</p>
  </div>
</footer>
<script src="<?= str_repeat('../', $depth ?? 0) ?>assets/js/main.js"></script>
</body>
</html>
