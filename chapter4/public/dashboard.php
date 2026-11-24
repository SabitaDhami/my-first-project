<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

require_once __DIR__ . '/../src/Core/Autoloader.php';

use App\Models\Post;
use App\Models\User;

if (empty($_SESSION['user_id'])) {
  header("Location: /php_workshop/chapter4/public/login.php");
  exit;
}

$userId = $_SESSION['user_id'];

$user = User::findById((int) $userId);

if (!$user) {
  header("Location: /php_workshop/chapter4/public/login.php");
  exit;
}

$username = $user['username'];


/* -------------------------------
   Update Bio
-------------------------------- */
if (isset($_POST['update_bio'])) {

  $bio = trim($_POST['bio'] ?? '');

  User::updateBio((int) $userId, $bio);

  header("Location: dashboard.php");
  exit;
}


/* -------------------------------
   Get Posts
-------------------------------- */

// If no posts are found, use empty array
$userPosts = Post::findByUserId($userId) ?? [];

$allPosts = Post::findAll() ?? [];

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="stylesheet" href="css/styles.css">

  <title>Dashboard</title>
</head>

<body>

  <header class="header">

    <div class="container header__container">

      <h1 class="header__title">KMC Buddies</h1>

      <nav class="nav">

        <a href="dashboard.php" class="nav__link">
          Dashboard
        </a>

        <a href="logout.php" class="nav__link">
          Logout
        </a>

      </nav>

    </div>

  </header>


  <main class="container">


    <!-- PROFILE SECTION -->

    <section class="section">

      <div class="card">

        <div class="card__header">

          <div class="card__avatar">
            <?= strtoupper($username[0]) ?>
          </div>

          <div class="card__username">
            @<?= htmlspecialchars($username) ?>
          </div>

        </div>


        <div class="card__body">

          <p>
            <b>Email:</b>
            <?= htmlspecialchars($user['email']) ?>
          </p>


          <p>
            <b>Bio:</b>

            <?= !empty($user['bio'])
              ? htmlspecialchars($user['bio'])
              : 'No Bio yet!' ?>
          </p>


          <p>
            <b>Member Since:</b>
            <?= htmlspecialchars($user['created_at']) ?>
          </p>

        </div>

      </div>

    </section>



    <!-- UPDATE BIO SECTION -->

    <section class="section">

      <h2 class="section__title">
        Update Your Bio
      </h2>

      <div class="card">

        <div class="card__body">

          <form method="POST">

            <div class="form-group">

              <textarea
                name="bio"
                placeholder="Write something about yourself..."
              ><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>

            </div>


            <input
              type="submit"
              name="update_bio"
              value="Save Bio"
              class="btn btn-primary"
            >

          </form>

        </div>

      </div>

    </section>



    <!-- CREATE POST SECTION (Modified with file upload support) -->

    <section class="section">

      <h2 class="section__title">
        Create a post!
      </h2>

      <div class="card">

        <div class="card__body">

          <!-- Added enctype="multipart/form-data" to allow file uploads -->
          <form action="create_post.php" method="post" enctype="multipart/form-data">

            <div class="form-group">

              <textarea
                name="content"
                placeholder="Whats on your mind, <?= htmlspecialchars($username) ?> ?"
              ></textarea>

            </div>

            <!-- Added new file input field for images and videos -->
            <div class="form-group" style="margin: 10px 0;">
              <label for="media_file" style="display:block; margin-bottom: 5px; font-weight: bold;">Add Photo or Video:</label>
              <input type="file" name="media_file" id="media_file" accept="image/*, video/*">
            </div>

            <input
              type="submit"
              value="Post"
              class="btn btn-primary"
            >

          </form>

        </div>

      </div>

    </section>



    <!-- USER POSTS (Modified to render uploaded photos/videos) -->

    <section class="section">

      <h2 class="section__title">
        Your Posts
      </h2>


      <?php if (empty($userPosts)): ?>

        <p>
          You have not created any posts yet.
        </p>

      <?php else: ?>

        <?php foreach ($userPosts as $post): ?>

          <div class="card post-card">

            <div class="card__header">

              <span class="card__avatar">
                <?= strtoupper($post['username'][0]) ?>
              </span>


              <div>

                <h3 class="card__username">
                  @<?= htmlspecialchars($post['username']) ?>
                </h3>

                <span class="card__meta">
                  <?= htmlspecialchars($post['created_at']) ?>
                </span>

              </div>

            </div>


            <div class="card__body">

              <?php if (!empty($post['content'])): ?>
                <p class="post-content">
                  <?= htmlspecialchars($post['content']) ?>
                </p>
              <?php endif; ?>

              <!-- Check if a media file exists in the database and display it -->
              <?php if (!empty($post['media_file'])): ?>
                <div class="post-media" style="margin-top: 10px;">
                  <?php 
                    $fileExtension = strtolower(pathinfo($post['media_file'], PATHINFO_EXTENSION));
                    $videoExtensions = ['mp4', 'mov', 'mpeg', 'avi'];
                    
                    if (in_array($fileExtension, $videoExtensions)): 
                  ?>
                    <!-- Render video player if file extension matches video formats -->
                    <video src="<?= htmlspecialchars($post['media_file']) ?>" controls style="max-width: 100%; border-radius: 8px;"></video>
                  <?php else: ?>
                    <!-- Render image element for photos -->
                    <img src="<?= htmlspecialchars($post['media_file']) ?>" alt="Post Media" style="max-width: 100%; height: auto; border-radius: 8px;">
                  <?php endif; ?>
                </div>
              <?php endif; ?>

            </div>

          </div>

        <?php endforeach; ?>

      <?php endif; ?>

    </section>


  </main>

</body>

</html>
