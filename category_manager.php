<?php
// category_manager.php - Gestion des catégories (degrés, niveaux, matières)
require_once 'config.php';
checkAdminAuth();

// ===== GESTION DES DEGRÉS =====

// Récupérer tous les degrés
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_degrees') {
    $pdo = connectDB();
    $stmt = $pdo->query("SELECT * FROM degrees ORDER BY id");
    $degrees = $stmt->fetchAll();
    jsonResponse(true, 'Degrés récupérés avec succès', $degrees);
}

// Créer un nouveau degré
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_degree') {
    $pdo = connectDB();
    
    if (empty($_POST['name'])) {
        jsonResponse(false, 'Le nom du degré est requis.');
    }
    
    $name = sanitize($_POST['name']);
    $description = isset($_POST['description']) ? sanitize($_POST['description']) : '';
    
    try {
        $stmt = $pdo->prepare("INSERT INTO degrees (name, description) VALUES (?, ?)");
        $stmt->execute([$name, $description]);
        $degreeId = $pdo->lastInsertId();
        
        jsonResponse(true, 'Degré créé avec succès', ['id' => $degreeId]);
    } catch (PDOException $e) {
        jsonResponse(false, 'Erreur lors de la création du degré: ' . $e->getMessage());
    }
}

// Mettre à jour un degré
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_degree' && isset($_POST['id'])) {
    $pdo = connectDB();
    $degreeId = (int)$_POST['id'];
    
    if (empty($_POST['name'])) {
        jsonResponse(false, 'Le nom du degré est requis.');
    }
    
    $name = sanitize($_POST['name']);
    $description = isset($_POST['description']) ? sanitize($_POST['description']) : '';
    
    try {
        $stmt = $pdo->prepare("UPDATE degrees SET name = ?, description = ? WHERE id = ?");
        $stmt->execute([$name, $description, $degreeId]);
        
        jsonResponse(true, 'Degré mis à jour avec succès');
    } catch (PDOException $e) {
        jsonResponse(false, 'Erreur lors de la mise à jour du degré: ' . $e->getMessage());
    }
}

// Supprimer un degré
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_degree' && isset($_POST['id'])) {
    $pdo = connectDB();
    $degreeId = (int)$_POST['id'];
    
    // Vérifier si des niveaux sont associés à ce degré
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM levels WHERE degree_id = ?");
    $stmt->execute([$degreeId]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        jsonResponse(false, 'Impossible de supprimer ce degré car il y a des niveaux associés.');
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM degrees WHERE id = ?");
        $stmt->execute([$degreeId]);
        
        jsonResponse(true, 'Degré supprimé avec succès');
    } catch (PDOException $e) {
        jsonResponse(false, 'Erreur lors de la suppression du degré: ' . $e->getMessage());
    }
}

// ===== GESTION DES NIVEAUX =====

// Récupérer tous les niveaux
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_levels') {
    $pdo = connectDB();
    
    $degreeFilter = '';
    $params = [];
    
    if (isset($_GET['degree_id']) && !empty($_GET['degree_id'])) {
        $degreeFilter = 'WHERE l.degree_id = ?';
        $params[] = (int)$_GET['degree_id'];
    }
    
    $sql = "SELECT l.*, d.name as degree_name 
            FROM levels l
            JOIN degrees d ON l.degree_id = d.id
            $degreeFilter
            ORDER BY l.id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $levels = $stmt->fetchAll();
    
    jsonResponse(true, 'Niveaux récupérés avec succès', $levels);
}

// Créer un nouveau niveau
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_level') {
    $pdo = connectDB();
    
    if (empty($_POST['name']) || empty($_POST['degree_id'])) {
        jsonResponse(false, 'Le nom et le degré sont requis.');
    }
    
    $name = sanitize($_POST['name']);
    $degreeId = (int)$_POST['degree_id'];
    $description = isset($_POST['description']) ? sanitize($_POST['description']) : '';
    
    // Vérifier si le degré existe
    $stmt = $pdo->prepare("SELECT id FROM degrees WHERE id = ?");
    $stmt->execute([$degreeId]);
    if (!$stmt->fetch()) {
        jsonResponse(false, 'Le degré spécifié n\'existe pas.');
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO levels (name, degree_id, description) VALUES (?, ?, ?)");
        $stmt->execute([$name, $degreeId, $description]);
        $levelId = $pdo->lastInsertId();
        
        jsonResponse(true, 'Niveau créé avec succès', ['id' => $levelId]);
    } catch (PDOException $e) {
        jsonResponse(false, 'Erreur lors de la création du niveau: ' . $e->getMessage());
    }
}

// Mettre à jour un niveau
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_level' && isset($_POST['id'])) {
    $pdo = connectDB();
    $levelId = (int)$_POST['id'];
    
    if (empty($_POST['name']) || empty($_POST['degree_id'])) {
        jsonResponse(false, 'Le nom et le degré sont requis.');
    }
    
    $name = sanitize($_POST['name']);
    $degreeId = (int)$_POST['degree_id'];
    $description = isset($_POST['description']) ? sanitize($_POST['description']) : '';
    
    // Vérifier si le degré existe
    $stmt = $pdo->prepare("SELECT id FROM degrees WHERE id = ?");
    $stmt->execute([$degreeId]);
    if (!$stmt->fetch()) {
        jsonResponse(false, 'Le degré spécifié n\'existe pas.');
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE levels SET name = ?, degree_id = ?, description = ? WHERE id = ?");
        $stmt->execute([$name, $degreeId, $description, $levelId]);
        
        jsonResponse(true, 'Niveau mis à jour avec succès');
    } catch (PDOException $e) {
        jsonResponse(false, 'Erreur lors de la mise à jour du niveau: ' . $e->getMessage());
    }
}

// Supprimer un niveau
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_level' && isset($_POST['id'])) {
    $pdo = connectDB();
    $levelId = (int)$_POST['id'];
    
    // Vérifier si des cours sont associés à ce niveau
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM courses WHERE level_id = ?");
    $stmt->execute([$levelId]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        jsonResponse(false, 'Impossible de supprimer ce niveau car il y a des cours associés.');
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM levels WHERE id = ?");
        $stmt->execute([$levelId]);
        
        jsonResponse(true, 'Niveau supprimé avec succès');
    } catch (PDOException $e) {
        jsonResponse(false, 'Erreur lors de la suppression du niveau: ' . $e->getMessage());
    }
}

// ===== GESTION DES MATIÈRES =====

// Récupérer toutes les matières
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_subjects') {
    $pdo = connectDB();
    $stmt = $pdo->query("SELECT * FROM subjects ORDER BY name");
    $subjects = $stmt->fetchAll();
    jsonResponse(true, 'Matières récupérées avec succès', $subjects);
}

// Créer une nouvelle matière
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_subject') {
    $pdo = connectDB();
    
    if (empty($_POST['name'])) {
        jsonResponse(false, 'Le nom de la matière est requis.');
    }
    
    $name = sanitize($_POST['name']);
    $description = isset($_POST['description']) ? sanitize($_POST['description']) : '';
    $icon = isset($_POST['icon']) ? sanitize($_POST['icon']) : 'fas fa-book';
    
    try {
        $stmt = $pdo->prepare("INSERT INTO subjects (name, description, icon) VALUES (?, ?, ?)");
        $stmt->execute([$name, $description, $icon]);
        $subjectId = $pdo->lastInsertId();
        
        jsonResponse(true, 'Matière créée avec succès', ['id' => $subjectId]);
    } catch (PDOException $e) {
        jsonResponse(false, 'Erreur lors de la création de la matière: ' . $e->getMessage());
    }
}

// Mettre à jour une matière
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_subject' && isset($_POST['id'])) {
    $pdo = connectDB();
    $subjectId = (int)$_POST['id'];
    
    if (empty($_POST['name'])) {
        jsonResponse(false, 'Le nom de la matière est requis.');
    }
    
    $name = sanitize($_POST['name']);
    $description = isset($_POST['description']) ? sanitize($_POST['description']) : '';
    $icon = isset($_POST['icon']) ? sanitize($_POST['icon']) : 'fas fa-book';
    
    try {
        $stmt = $pdo->prepare("UPDATE subjects SET name = ?, description = ?, icon = ? WHERE id = ?");
        $stmt->execute([$name, $description, $icon, $subjectId]);
        
        jsonResponse(true, 'Matière mise à jour avec succès');
    } catch (PDOException $e) {
        jsonResponse(false, 'Erreur lors de la mise à jour de la matière: ' . $e->getMessage());
    }
}

// Supprimer une matière
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_subject' && isset($_POST['id'])) {
    $pdo = connectDB();
    $subjectId = (int)$_POST['id'];
    
    // Vérifier si des cours sont associés à cette matière
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM courses WHERE subject_id = ?");
    $stmt->execute([$subjectId]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        jsonResponse(false, 'Impossible de supprimer cette matière car il y a des cours associés.');
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM subjects WHERE id = ?");
        $stmt->execute([$subjectId]);
        
        jsonResponse(true, 'Matière supprimée avec succès');
    } catch (PDOException $e) {
        jsonResponse(false, 'Erreur lors de la suppression de la matière: ' . $e->getMessage());
    }
}