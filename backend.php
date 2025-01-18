<?php
// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

// Upload directory
$uploadDir = 'uploads/';

// Ensure the uploads folder exists
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Handle requests
$requestMethod = $_SERVER['REQUEST_METHOD'];

if ($requestMethod === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'createFolder') {
        $folderName = trim($_POST['folderName']);
        $currentPath = $_POST['currentPath'];
        $folderPath = $uploadDir . $currentPath . '/' . $folderName;

        if (!file_exists($folderPath)) {
            mkdir($folderPath, 0777, true);
            echo json_encode(["message" => "Folder created successfully."]);
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Folder already exists."]);
        }
    } elseif ($action === 'uploadFile') {
        if (isset($_FILES['file'])) {
            $currentPath = $_POST['currentPath'];
            $fileName = basename($_FILES['file']['name']);
            $fileTmp = $_FILES['file']['tmp_name'];
            $targetFile = $uploadDir . $currentPath . '/' . $fileName;

            if (move_uploaded_file($fileTmp, $targetFile)) {
                echo json_encode(["message" => "File uploaded successfully."]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "File upload failed."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "No file uploaded."]);
        }
    } elseif ($action === 'renameFile') {
        $currentPath = $_POST['currentPath'];
        $oldName = $uploadDir . $currentPath . '/' . $_POST['oldName'];
        $newName = $uploadDir . $currentPath . '/' . $_POST['newName'];

        if (file_exists($oldName)) {
            rename($oldName, $newName);
            echo json_encode(["message" => "File renamed successfully."]);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "File not found."]);
        }
    } elseif ($action === 'moveFile') {
        $currentPath = $_POST['currentPath'];
        $fileName = $_POST['fileName'];
        $destinationFolder = $uploadDir . $_POST['destinationFolder'];

        if (!file_exists($destinationFolder)) {
            mkdir($destinationFolder, 0777, true);
        }

        $sourceFile = $uploadDir . $currentPath . '/' . $fileName;
        $destinationFile = $destinationFolder . '/' . $fileName;

        if (file_exists($sourceFile)) {
            rename($sourceFile, $destinationFile);
            echo json_encode(["message" => "File moved successfully."]);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "File not found."]);
        }
    }
} elseif ($requestMethod === 'GET') {
    // Fetch files/folders from the current path
    $currentPath = $_GET['currentPath'] ?? '';
    $folderPath = $uploadDir . $currentPath;

    if (file_exists($folderPath)) {
        $items = array_diff(scandir($folderPath), ['.', '..']);
        $result = [];

        foreach ($items as $item) {
            $isDir = is_dir($folderPath . '/' . $item);
            $result[] = ["name" => $item, "isDir" => $isDir];
        }

        echo json_encode($result);
    } else {
        http_response_code(404);
        echo json_encode(["message" => "Folder not found."]);
    }
} elseif ($requestMethod === 'DELETE') {
    // Parse JSON input from the body
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['currentPath'], $data['fileName'])) {
        $currentPath = $data['currentPath'];
        $fileName = $data['fileName'];
        $filePath = $uploadDir . $currentPath . '/' . $fileName;

        if (file_exists($filePath)) {
            if (unlink($filePath)) {
                echo json_encode(["message" => "File deleted successfully."]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Failed to delete file."]);
            }
        } else {
            http_response_code(404);
            echo json_encode(["message" => "File not found."]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["message" => "Invalid request. Missing 'currentPath' or 'fileName'."]);
    }
} elseif ($requestMethod === 'GET' && isset($_GET['viewFile'])) {
    // View file (allow access to file content)
    $currentPath = $_GET['currentPath'] ?? '';
    $fileName = $_GET['fileName'] ?? '';
    $filePath = $uploadDir . $currentPath . '/' . $fileName;

    if (file_exists($filePath)) {
        // Serve the file content
        header('Content-Type: ' . mime_content_type($filePath)); // Detect mime type
        header('Content-Length: ' . filesize($filePath)); // Set content length
        readfile($filePath); // Output the file
        exit;
    } else {
        http_response_code(404);
        echo json_encode(["message" => "File not found."]);
    }
} else {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed."]);
}
?>
