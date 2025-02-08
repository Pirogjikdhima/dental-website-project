<div id="navigation">
  <nav>
    <div class="nav-toggle" id="navToggle">
      <span></span>
      <span></span>
      <span></span>
    </div>
    <ul id="navMenu">
      <li><a href="./home.php">Home</a></li>
      <li><a href="./services.php">Services</a></li>
      <li><a href="./faq.php">FAQ</a></li>
      <li><a href="./shop.php">Shop</a></li>
      <li><a href="./reviews.php">Reviews</a></li>
      <li id="log-in-item">
          <?php
          if (isset($_SESSION['user_id'])) {
              $firstName = htmlspecialchars($_SESSION['first_name']);
              $lastName = htmlspecialchars($_SESSION['last_name']);
              echo "<a href='./user.php'>{$firstName} {$lastName}</a>";
          } else {
              echo "<a href='./signin.html'>Log In</a>";
          }
          ?>
      </li>
    </ul>
  </nav>
  <hr/>
</div>
