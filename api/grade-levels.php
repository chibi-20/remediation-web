<?php
require_once '../config.php';
require_once '../security-middleware.php';

// Apply API security checks
SecurityMiddleware::checkAPISecurity();

header('Content-Type: application/json; charset=utf-8');

// Check admin authentication
session_start(); // Use regular session_start() like admin-login.php
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    jsonResponse(false, 'Admin authentication required');
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    if ($method === 'GET') {
        // Get all grade levels with their sections
        $stmt = $pdo->query("
            SELECT 
                level,
                GROUP_CONCAT(section_name) as sections
            FROM grade_sections 
            GROUP BY level 
            ORDER BY level ASC
        ");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $gradeLevels = [];
        foreach ($results as $row) {
            $sections = [];
            if ($row['sections']) {
                $sectionNames = explode(',', $row['sections']);
                foreach ($sectionNames as $name) {
                    $sections[] = ['name' => trim($name)];
                }
            }
            
            $gradeLevels[] = [
                'level' => (int)$row['level'],
                'sections' => $sections
            ];
        }
        
        jsonResponse(true, 'Grade levels retrieved successfully', $gradeLevels);
        
    } elseif ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? '';
        
        if ($action === 'add_grade') {
            $level = (int)($input['level'] ?? 0);
            
            if ($level < 7 || $level > 12) {
                jsonResponse(false, 'Invalid grade level. Must be between 7-12.');
            }
            
            // Check if grade level already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM grade_sections WHERE level = ?");
            $stmt->execute([$level]);
            
            if ($stmt->fetchColumn() > 0) {
                jsonResponse(false, "Grade $level already exists.");
            }
            
            // Create a placeholder entry for the grade level (will be replaced when first section is added)
            $stmt = $pdo->prepare("INSERT INTO grade_sections (level, section_name) VALUES (?, ?)");
            $stmt->execute([$level, '']);
            
            jsonResponse(true, "Grade $level added successfully");
            
        } elseif ($action === 'add_section') {
            $level = (int)($input['level'] ?? 0);
            $sectionName = trim($input['section_name'] ?? '');
            
            if (empty($sectionName)) {
                jsonResponse(false, 'Section name is required.');
            }
            
            // Check if section already exists in this grade
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM grade_sections WHERE level = ? AND section_name = ?");
            $stmt->execute([$level, $sectionName]);
            
            if ($stmt->fetchColumn() > 0) {
                jsonResponse(false, "Section '$sectionName' already exists in Grade $level.");
            }
            
            // Remove empty placeholder if it exists
            $stmt = $pdo->prepare("DELETE FROM grade_sections WHERE level = ? AND section_name = ''");
            $stmt->execute([$level]);
            
            // Add the new section
            $stmt = $pdo->prepare("INSERT INTO grade_sections (level, section_name) VALUES (?, ?)");
            $stmt->execute([$level, $sectionName]);
            
            jsonResponse(true, "Section '$sectionName' added to Grade $level successfully");
            
        } else {
            jsonResponse(false, 'Invalid action');
        }
        
    } elseif ($method === 'DELETE') {
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? '';
        
        if ($action === 'delete_grade') {
            $level = (int)($input['level'] ?? 0);
            
            // Delete all sections for this grade level
            $stmt = $pdo->prepare("DELETE FROM grade_sections WHERE level = ?");
            $stmt->execute([$level]);
            
            if ($stmt->rowCount() > 0) {
                jsonResponse(true, "Grade $level and all its sections deleted successfully");
            } else {
                jsonResponse(false, "Grade $level not found");
            }
            
        } elseif ($action === 'delete_section') {
            $level = (int)($input['level'] ?? 0);
            $sectionName = trim($input['section_name'] ?? '');
            
            $stmt = $pdo->prepare("DELETE FROM grade_sections WHERE level = ? AND section_name = ?");
            $stmt->execute([$level, $sectionName]);
            
            if ($stmt->rowCount() > 0) {
                // If this was the last section, add empty placeholder
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM grade_sections WHERE level = ?");
                $stmt->execute([$level]);
                
                if ($stmt->fetchColumn() == 0) {
                    $stmt = $pdo->prepare("INSERT INTO grade_sections (level, section_name) VALUES (?, ?)");
                    $stmt->execute([$level, '']);
                }
                
                jsonResponse(true, "Section '$sectionName' deleted from Grade $level successfully");
            } else {
                jsonResponse(false, "Section '$sectionName' not found in Grade $level");
            }
            
        } else {
            jsonResponse(false, 'Invalid action');
        }
        
    } else {
        jsonResponse(false, 'Method not allowed');
    }
    
} catch (Exception $e) {
    error_log("Grade levels API error: " . $e->getMessage());
    jsonResponse(false, 'Database error: ' . $e->getMessage());
}
?>
