<?php
// course_manager.php - Gestion des cours
require_once 'config.php';
checkAdminAuth();

// Récupérer tous les cours
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_courses') {
    $pdo = connectDB();
    
    // Filtrage
    $where = [];
    $params = [];
    
    if (!empty($_GET['degree'])) {
        $where[] = 'c.degree_id = ?';
        $params[] = sanitize($_GET['degree']);
    }
    
    if (!empty($_GET['level'])) {
        $where[] = 'c.level_id = ?';
        $params[] = sanitize($_GET['level']);
    }
    
    if (!empty($_GET['subject'])) {
        $where[] = 'c.subject_id = ?';
        $params[] = sanitize($_GET['subject']);
    }
    
    if (!empty($_GET['search'])) {
        $where[] = 'c.title LIKE ?';
        $params[] = '%' . sanitize($_GET['search']) . '%';
    }
    
    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    $sql = "SELECT c.*, d.name as degree_name, l.name as level_name, s.name as subject_name
            FROM courses c
            JOIN degrees d ON c.degree_id = d.id
            JOIN levels l ON c.level_id = l.id
            JOIN subjects s ON c.subject_id = s.id
            $whereClause
            ORDER BY c.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $courses = $stmt->fetchAll();
    
    jsonResponse(true, 'Cours récupérés avec succès', $courses);
}

// Créer un nouveau cours
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_course') {
    $pdo = connectDB();
    
    // Validation des champs requis
    if (empty($_POST['title']) || empty($_POST['degree_id']) || empty($_POST['level_id']) || 
        empty($_POST['subject_id']) || empty($_POST['content'])) {
        jsonResponse(false, 'Tous les champs obligatoires doivent être remplis.');
    }
    
    $title = sanitize($_POST['title']);
    $description = isset($_POST['description']) ? sanitize($_POST['description']) : '';
    $content = $_POST['content']; // Pas de sanitize pour permettre le HTML du contenu riche
    $degree_id = (int)$_POST['degree_id'];
    $level_id = (int)$_POST['level_id'];
    $subject_id = (int)$_POST['subject_id'];
    $status = isset($_POST['status']) ? sanitize($_POST['status']) : 'draft';
    $display_order = isset($_POST['display_order']) ? (int)$_POST['display_order'] : 1;
    
    // Traitement de l'image de couverture si fournie
    $thumbnail = '';
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/thumbnails/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileName = time() . '_' . basename($_FILES['thumbnail']['name']);
        $uploadFile = $uploadDir . $fileName;
        
        // Vérifier le type de fichier
        $fileType = mime_content_type($_FILES['thumbnail']['tmp_name']);
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        
        if (!in_array($fileType, $allowedTypes)) {
            jsonResponse(false, 'Type de fichier non autorisé. Seuls JPG, PNG et GIF sont acceptés.');
        }
        
        if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $uploadFile)) {
            $thumbnail = $uploadFile;
        } else {
            jsonResponse(false, 'Erreur lors du téléchargement de l\'image.');
        }
    }
    
    try {
        $sql = "INSERT INTO courses (title, description, content, degree_id, level_id, subject_id, 
                thumbnail, status, display_order) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$title, $description, $content, $degree_id, $level_id, $subject_id, 
                       $thumbnail, $status, $display_order]);
        
        $courseId = $pdo->lastInsertId();
        
        jsonResponse(true, 'Cours créé avec succès', ['id' => $courseId]);
    } catch (PDOException $e) {
        jsonResponse(false, 'Erreur lors de la création du cours: ' . $e->getMessage());
    }
}

// Récupérer un cours spécifique
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_course' && isset($_GET['id'])) {
    $pdo = connectDB();
    $courseId = (int)$_GET['id'];
    
    $sql = "SELECT c.*, d.name as degree_name, l.name as level_name, s.name as subject_name
            FROM courses c
            JOIN degrees d ON c.degree_id = d.id
            JOIN levels l ON c.level_id = l.id
            JOIN subjects s ON c.subject_id = s.id
            WHERE c.id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$courseId]);
    $course = $stmt->fetch();
    
    if ($course) {
        jsonResponse(true, 'Cours récupéré avec succès', $course);
    } else {
        jsonResponse(false, 'Cours non trouvé');
    }
}

// Mettre à jour un cours
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_course' && isset($_POST['id'])) {
    $pdo = connectDB();
    $courseId = (int)$_POST['id'];
    
    // Validation des champs requis
    if (empty($_POST['title']) || empty($_POST['degree_id']) || empty($_POST['level_id']) || 
        empty($_POST['subject_id']) || empty($_POST['content'])) {
        jsonResponse(false, 'Tous les champs obligatoires doivent être remplis.');
    }
    
    $title = sanitize($_POST['title']);
    $description = isset($_POST['description']) ? sanitize($_POST['description']) : '';
    $content = $_POST['content']; // Pas de sanitize pour permettre le HTML du contenu riche
    $degree_id = (int)$_POST['degree_id'];
    $level_id = (int)$_POST['level_id'];
    $subject_id = (int)$_POST['subject_id'];
    $status = isset($_POST['status']) ? sanitize($_POST['status']) : 'draft';
    $display_order = isset($_POST['display_order']) ? (int)$_POST['display_order'] : 1;
    
    // Récupérer le cours existant pour vérifier l'image
    $stmt = $pdo->prepare("SELECT thumbnail FROM courses WHERE id = ?");
    $stmt->execute([$courseId]);
    $existingCourse = $stmt->fetch();
    
    if (!$existingCourse) {
        jsonResponse(false, 'Cours non trouvé');
    }
    
    $thumbnail = $existingCourse['thumbnail'];
    
    // Traitement de l'image de couverture si fournie
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/thumbnails/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileName = time() . '_' . basename($_FILES['thumbnail']['name']);
        $uploadFile = $uploadDir . $fileName;
        
        // Vérifier le type de fichier
        $fileType = mime_content_type($_FILES['thumbnail']['tmp_name']);
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        
        if (!in_array($fileType, $allowedTypes)) {
            jsonResponse(false, 'Type de fichier non autorisé. Seuls JPG, PNG et GIF sont acceptés.');
        }
        
        if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $uploadFile)) {
            // Supprimer l'ancienne image si elle existe
            if (!empty($thumbnail) && file_exists($thumbnail)) {
                unlink($thumbnail);
            }
            $thumbnail = $uploadFile;
        } else {
            jsonResponse(false, 'Erreur lors du téléchargement de l\'image.');
        }
    }
    
    try {
        $sql = "UPDATE courses SET 
                title = ?, 
                description = ?, 
                content = ?, 
                degree_id = ?, 
                level_id = ?, 
                subject_id = ?, 
                thumbnail = ?, 
                status = ?, 
                display_order = ?,
                updated_at = CURRENT_TIMESTAMP
                WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$title, $description, $content, $degree_id, $level_id, $subject_id, 
                       $thumbnail, $status, $display_order, $courseId]);
        
        jsonResponse(true, 'Cours mis à jour avec succès');
    } catch (PDOException $e) {
        jsonResponse(false, 'Erreur lors de la mise à jour du cours: ' . $e->getMessage());
    }
}

// Supprimer un cours
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_course' && isset($_POST['id'])) {
    $pdo = connectDB();
    $courseId = (int)$_POST['id'];
    
    // Récupérer le cours pour supprimer l'image associée
    $stmt = $pdo->prepare("SELECT thumbnail FROM courses WHERE id = ?");
    $stmt->execute([$courseId]);
    $course = $stmt->fetch();
    
    if (!$course) {
        jsonResponse(false, 'Cours non trouvé');
    }
    
    try {
        // Supprimer l'image si elle existe
        if (!empty($course['thumbnail']) && file_exists($course['thumbnail'])) {
            unlink($course['thumbnail']);
        }
        
        // Supprimer le cours
        $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
        $stmt->execute([$courseId]);
        
        jsonResponse(true, 'Cours supprimé avec succès');
    } catch (PDOException $e) {
        jsonResponse(false, 'Erreur lors de la suppression du cours: ' . $e->getMessage());
    }
}