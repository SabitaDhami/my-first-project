<?php

namespace App\Controllers;

use App\Models\Post;

class PostController{
  private array $errors = [];

  public function create() :array{
    if (empty($_SESSION['user_id'])){
      header("Location: /chater4/public/login.php");
      exit;
    }

    if ($_SERVER['REQUEST_METHOD'] == "POST"){
      $content = trim($_POST['content'])?:"";
      $userId = $_SESSION['user_id'];
      $imageName = null; // Suru ma image chhaina bhanera null banako

      // 1. Text content validation
      if (empty($content) && empty($_FILES['image']['name'])){
        $this->errors[] = "Post content or an image is required";
      } elseif (strlen($content) > 1000) {
        $this->errors[] = "Post length can not be more than 1000 characters";
      }

      // 2. Image upload logic (Yadi user le photo select gareko chha bhane)
      if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] == UPLOAD_ERRORS_OK) {
        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileName = $_FILES['image']['name'];
        $fileSize = $_FILES['image']['size'];
        
        // Photo ko extension check garne (jpg, jpeg, png, gif matra allow garne)
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($fileExtension, $allowedExtensions)) {
          $this->errors[] = "Invalid image type. Only JPG, JPEG, PNG, and GIF are allowed.";
        }
        
        // Size check garne (e.g., max 2MB)
        if ($fileSize > 2 * 1024 * 1024) {
          $this->errors[] = "Image size should be less than 2MB.";
        }

        // Sabai thik chha bhane photo lai naya unique naam diyera uploads folder ma pathaune
        if (empty($this->errors)) {
          $uploadDir = __DIR__ . '/../../public/uploads/';
          
          // uploads folder chhaina bhane afei banchha
          if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
          }

          $imageName = time() . '_' . uniqid() . '.' . $fileExtension;
          $destPath = $uploadDir . $imageName;

          if (!move_uploaded_file($fileTmpPath, $destPath)) {
            $this->errors[] = "Failed to upload image.";
          }
        }
      }

      // 3. Database ma save garne logic
      if (empty($this->errors)){
        // Post::create ma ahile $imageName pani pathako chha
        $newPostId = Post::create($userId, $content, $imageName);
        if ($newPostId){
          $_SESSION['flash_messages']['success'] = "Post created successfully";
          header("Location: /php_workshop/chapter4/public/dashboard.php");
          exit;
        } else {
          $this->errors[] = "Post could not be created";
        }
      }
    }
    return [
      'errors' => $this->errors
    ];
  }
}
