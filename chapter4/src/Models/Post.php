<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Post {
  private int $userId;
  private string $content;
  private ?string $mediaFile; // Added property to hold optional media files

  public function __construct(int $userId, string $content, ?string $mediaFile = null)
  {
    $this->userId = $userId;
    $this->content = $content;
    $this->mediaFile = $mediaFile;
  }

  /**
   * Create a new post entry in the database.
   * Updated to accept an optional third argument for the media file path.
   */
  public static function create(int $userId, string $content, ?string $mediaFile = null): int|false {
    $pdo = Database::getInstance()->getConnection();
    
    // Included media_file column in the INSERT statement
    $stmt = $pdo->prepare("INSERT INTO posts (user_id, content, media_file) VALUES (:user_id, :content, :media_file)");
    
    $result = $stmt->execute([
      'user_id' => $userId,
      'content' => $content,
      'media_file' => $mediaFile
    ]);
    
    return $result ? (int) $pdo->lastInsertId() : false;
  }

  public static function findByUserId(int $userId): ?array{
    $pdo = Database::getInstance()->getConnection();
    $stmt = $pdo->prepare(
      "SELECT posts.*, users.username 
      FROM posts
      JOIN users ON users.id = posts.user_id 
      WHERE user_id = :user_id
      ORDER BY created_at DESC"
    );
    $stmt->execute(['user_id' => $userId]);
    $result = $stmt->fetchAll();
    return $result ?: null;
  }

  public static function findAll(): ?array{
    $pdo = Database::getInstance()->getConnection();
    $stmt = $pdo->prepare(
      "SELECT posts.*, users.username 
      FROM posts
      JOIN users ON users.id = posts.user_id 
      ORDER BY created_at DESC"
    );
    $stmt->execute();
    $result = $stmt->fetchAll();
    return $result ?: null;
  }
}
