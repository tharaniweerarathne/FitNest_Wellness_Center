<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fitnestwellness";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['category'])) {
    $category = urldecode($_GET['category']);

    $stmt = $conn->prepare("SELECT * FROM blogs WHERE category = ? ORDER BY created_at DESC");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("s", $category);
    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $blogs = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $blogs[] = [
                'title' => $row['title'],
                'content' => $row['content'],
                'image' => $row['image'],
                'author' => $row['author'],
                'created_at' => date("F j, Y, g:i a", strtotime($row['created_at'])),
                'category' => $row['category']
            ];
        }
    } else {
        echo "<p>No blogs found under this category. Check if the category exists in the database.</p>";
        exit();
    }
    
    $stmt->close();
} else {
    echo "<p>No category selected. Please select a category from the blog page.</p>";
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blogs - <?= htmlspecialchars($category) ?></title>
    <style>


body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f8f9fa;
    color: #333;
    line-height: 1.6;
}

.container {
    width: 80%;
    max-width: 1200px;
    margin: 20px auto;
    padding: 20px;
    border: 2px solid #dee2e6;
    border-radius: 8px;
    background-color: #fff;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: box-shadow 0.3s ease;
}

.container:hover {
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}

.main-heading {
    text-align: center;
    font-size: 42px;
    font-weight: bold;
    margin: 30px 0 40px;
    color: #2c3e50;
    position: relative;
    padding-bottom: 15px;
}

.main-heading::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 100px;
    height: 4px;
    background: linear-gradient(90deg, #D91656,rgb(182, 21, 185));
    border-radius: 2px;
}

.blog-post {
    display: flex;
    margin-bottom: 50px;
    border-bottom: 1px solid #eee;
    padding-bottom: 40px;
    transition: transform 0.3s ease;
}

.blog-post:hover {
    transform: translateY(-5px);
}

.blog-post:last-child {
    border-bottom: none;
}

.blog-image {
    width: 300px;
    flex-shrink: 0;
    margin-right: 30px;
    border-radius: 6px;
    overflow: hidden;
    box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
    transition: box-shadow 0.3s ease;
}

.blog-image:hover {
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.blog-image img {
    width: 100%;
    height: auto;
    display: block;
    transition: transform 0.3s ease;
}

.blog-image:hover img {
    transform: scale(1.05);
}

.blog-content {
    flex-grow: 1;
}

.blog-title {
    font-size: 30px;
    font-weight: bold;
    margin: 0 0 20px;
    color:  #AF1740;
    transition: color 0.3s ease;
}

.blog-title a {
    color: inherit;
    text-decoration: none;
    background: linear-gradient(90deg, #D91656,rgb(204, 46, 201));
    background-clip: text;
    -webkit-background-clip: text;
    color: transparent;
    transition: all 0.3s ease;
}

.blog-title a:hover {
    background: linear-gradient(90deg, #D91656,rgb(197, 40, 173));
    background-clip: text;
    text-shadow: 0 0 10px rgba(46, 204, 113, 0.2);
}

.blog-text {
    font-size: 16px;
    line-height: 1.7;
    color: #555;
    margin-bottom: 20px;
}

.blog-meta {
    margin-top: 20px;
    display: flex;
    gap: 20px;
    font-size: 16px;
    color: #555;
}

.blog-author {
    font-weight: bold;
    color: #D91656;
    transition: color 0.3s ease;
}

.blog-author:hover {
    color: #D91656;
    text-decoration: underline;
    cursor: pointer;
}

.blog-date {
    color: #7f8c8d;
    font-style: italic;
}

.read-more {
    display: inline-block;
    margin-top: 15px;
    color:  #AF1740;
    font-weight: bold;
    text-decoration: none;
    padding: 8px 16px;
    border: 2px solid #D91656;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.read-more:hover {
    background-color:  #D91656;
    color: white;
    text-decoration: none;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px  #AF1740;
}

@media (max-width: 768px) {
    .container {
        width: 90%;
    }
    
    .blog-post {
        flex-direction: column;
    }
    
    .blog-image {
        width: 100%;
        margin-right: 0;
        margin-bottom: 20px;
    }
    
    .main-heading {
        font-size: 32px;
    }
    
    .blog-title {
        font-size: 26px;
    }
}

@media (max-width: 480px) {
    .container {
        width: 95%;
        padding: 15px;
    }
    
    .main-heading {
        font-size: 28px;
        margin: 20px 0 30px;
    }
    
    .blog-title {
        font-size: 22px;
    }
    
    .blog-meta {
        flex-direction: column;
        gap: 5px;
    }
    
    .read-more {
        width: 100%;
        text-align: center;
    }
}

.blog-post:hover .blog-title a {
    text-shadow: 0 0 15px rgba(46, 204, 113, 0.3);
}

.blog-post:hover .blog-author {
    transform: translateX(5px);
}

.blog-post.featured {
    position: relative;
    border-left: 5px solid #e74c3c;
    padding-left: 15px;
    background-color: #fef9f9;
}

.blog-post.featured::before {
    content: 'Featured';
    position: absolute;
    top: -15px;
    right: 0;
    background-color: #e74c3c;
    color: white;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: bold;
}
    </style>
</head>
<body>
<h1 class="main-heading">Blogs (<?= htmlspecialchars($category) ?>)</h1>

<?php if (!empty($blogs)): ?>
    <?php foreach ($blogs as $blog): ?>

        <div class="container">
            <article class="blog-post">
                <?php if (!empty($blog['image'])): ?>
                    <div class="blog-image">
                        <img src="<?= htmlspecialchars($blog['image']) ?>" alt="<?= htmlspecialchars($blog['title']) ?>">
                    </div>
                <?php endif; ?>
                
                <div class="blog-content">
                    <h2 class="blog-title"><?= htmlspecialchars($blog['title']) ?></h2>
                    
                    <div class="blog-text">
                        <?php 
                       
                        $paragraphs = preg_split('/\n|\r\n?/', $blog['content']);
                        foreach ($paragraphs as $paragraph):
                            if (!empty(trim($paragraph))):
                        ?>
                            <p><?= htmlspecialchars(trim($paragraph)) ?></p>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </div>
                    
                    <div class="blog-meta">
                        <span class="blog-author">Written by: <?= htmlspecialchars($blog['author']) ?></span>
                        <span class="blog-date">Date and time: <em><?= $blog['created_at'] ?></em></span>
                    </div>
                </div>
            </article>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="container">
        <p>No blogs found in this category.</p>
    </div>
<?php endif; ?>
</body>
</html>