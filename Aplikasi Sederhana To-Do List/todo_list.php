<?php
/**
 * File: todo_list.php
 * Aplikasi To-Do List sederhana dengan:
 * - Penyimpanan data dalam array
 * - Fitur checkbox untuk menandai selesai
 * - Tombol hapus tugas
 * - Menggunakan Bootstrap 5
 */

// ==================== KONFIGURASI & INISIALISASI ====================
class TodoApp {
    private $tasks = [];
    private $dataFile = 'todo_data.json';

    public function __construct() {
        $this->loadTasks();
    }

    // ==================== MANAJEMEN PENYIMPANAN ====================
    private function loadTasks() {
        if (file_exists($this->dataFile)) {
            $data = file_get_contents($this->dataFile);
            $this->tasks = json_decode($data, true) ?: [];
        }
    }

    private function saveTasks() {
        file_put_contents($this->dataFile, json_encode($this->tasks));
    }

    // ==================== OPERASI TUGAS ====================
    public function addTask($title, $description = '') {
        if (empty(trim($title))) return false;

        $newTask = [
            'id' => uniqid(),
            'title' => htmlspecialchars(trim($title)),
            'description' => htmlspecialchars(trim($description)),
            'created_at' => date('Y-m-d H:i:s'),
            'completed' => false
        ];

        array_unshift($this->tasks, $newTask);
        $this->saveTasks();
        return true;
    }

    public function toggleTask($taskId) {
        foreach ($this->tasks as &$task) {
            if ($task['id'] === $taskId) {
                $task['completed'] = !$task['completed'];
                $this->saveTasks();
                return true;
            }
        }
        return false;
    }

    public function deleteTask($taskId) {
        foreach ($this->tasks as $key => $task) {
            if ($task['id'] === $taskId) {
                unset($this->tasks[$key]);
                $this->saveTasks();
                return true;
            }
        }
        return false;
    }

    public function getTasks() {
        return $this->tasks;
    }
}

// ==================== PROSES REQUEST ====================
$todoApp = new TodoApp();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_task'])) {
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        
        if ($todoApp->addTask($title, $description)) {
            $message = ['type' => 'success', 'text' => 'Tugas berhasil ditambahkan!'];
        } else {
            $message = ['type' => 'danger', 'text' => 'Judul tugas tidak boleh kosong!'];
        }
    }
    
    if (isset($_POST['toggle_task'])) {
        $taskId = $_POST['task_id'] ?? '';
        $todoApp->toggleTask($taskId);
    }
    
    if (isset($_POST['delete_task'])) {
        $taskId = $_POST['task_id'] ?? '';
        if ($todoApp->deleteTask($taskId)) {
            $message = ['type' => 'success', 'text' => 'Tugas berhasil dihapus!'];
        }
    }
}

$tasks = $todoApp->getTasks();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Aplikasi To-Do List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .completed-task {
            text-decoration: line-through;
            opacity: 0.7;
        }
        .task-card {
            transition: all 0.3s ease;
        }
        .task-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h1 class="h4 mb-0">Aplikasi To-Do List</h1>
                    </div>
                    
                    <div class="card-body">
                        <?php if (isset($message)): ?>
                            <div class="alert alert-<?= $message['type'] ?> alert-dismissible fade show">
                                <?= $message['text'] ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Form Tambah Tugas -->
                        <form method="POST" class="mb-4">
                            <div class="mb-3">
                                <label for="title" class="form-label">Judul Tugas*</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Deskripsi</label>
                                <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                            </div>
                            
                            <button type="submit" name="add_task" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Tambah Tugas
                            </button>
                        </form>
                        
                        <hr>
                        
                        <!-- Daftar Tugas -->
                        <h2 class="h5 mb-3">Daftar Tugas</h2>
                        
                        <?php if (empty($tasks)): ?>
                            <div class="alert alert-info">Belum ada tugas. Tambahkan tugas baru di atas.</div>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($tasks as $task): ?>
                                    <div class="list-group-item task-card mb-2 <?= $task['completed'] ? 'completed-task' : '' ?>">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="form-check">
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                                                    <input class="form-check-input" type="checkbox" 
                                                           id="task-<?= $task['id'] ?>" 
                                                           <?= $task['completed'] ? 'checked' : '' ?>
                                                           onChange="this.form.submit()">
                                                    <label class="form-check-label" for="task-<?= $task['id'] ?>">
                                                        <strong><?= htmlspecialchars($task['title']) ?></strong>
                                                    </label>
                                                    <input type="hidden" name="toggle_task">
                                                </form>
                                            </div>
                                            
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                                                <button type="submit" name="delete_task" class="btn btn-sm btn-danger">
                                                    <i class="bi bi-trash"></i> Hapus
                                                </button>
                                            </form>
                                        </div>
                                        
                                        <?php if (!empty($task['description'])): ?>
                                            <div class="mt-2 text-muted">
                                                <?= nl2br(htmlspecialchars($task['description'])) ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="mt-2 text-muted small">
                                            <i class="bi bi-clock"></i> Dibuat: <?= $task['created_at'] ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="card-footer text-muted small">
                        Total Tugas: <?= count($tasks) ?> | 
                        Selesai: <?= count(array_filter($tasks, fn($task) => $task['completed'])) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-submit form saat checkbox diubah
        document.querySelectorAll('.form-check-input').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                this.form.submit();
            });
        });
    </script>
</body>
</html>
