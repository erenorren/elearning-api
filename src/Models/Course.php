<?php
// src/Models/Course.php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use App\Traits\Validatable;
use App\Interfaces\Publishable;
use App\Interfaces\EnrollAble;

/**
 * Course Model
 */
class Course extends Model implements Publishable, EnrollAble
{
    use Validatable;

    private string $courseCode = '';
    private string $title = '';
    private string $description = '';
    private string $category = '';
    private int $maxStudents = 0;
    private int $currentEnrolled = 0;
    private string $status = 'draft'; // draft, published, archived

    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->fill($data);
        }
    }

    private function fill(array $data): void
    {
        $this->courseCode      = $data['course_code']      ?? $this->courseCode;
        $this->title           = $data['title']            ?? $this->title;
        $this->description     = $data['description']      ?? $this->description;
        $this->category        = $data['category']         ?? $this->category;
        $this->maxStudents     = isset($data['max_students']) ? (int)$data['max_students'] : $this->maxStudents;
        $this->currentEnrolled = isset($data['current_enrolled']) ? (int)$data['current_enrolled'] : $this->currentEnrolled;
        $this->status          = $data['status']           ?? $this->status;
    }

    public function validate(): bool
    {
        $this->clearErrors();

        $this->validateRequired('course_code', $this->courseCode, 'Course code');
        $this->validateRequired('title', $this->title, 'Title');
        $this->validateRequired('description', $this->description, 'Description');
        $this->validateRequired('category', $this->category, 'Category');

        if ($this->maxStudents < 0) {
            $this->addError('max_students', 'max_students must be >= 0');
        }

        if ($this->currentEnrolled < 0) {
            $this->addError('current_enrolled', 'current_enrolled must be >= 0');
        }

        if ($this->currentEnrolled > $this->maxStudents && $this->maxStudents > 0) {
            $this->addError('current_enrolled', 'current_enrolled cannot exceed max_students');
        }

        return !$this->hasErrors();
    }

    // Publishable
    public function publish(): void
    {
        $this->status = 'published';
    }

    public function unpublish(): void
    {
        $this->status = 'draft';
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    // EnrollAble
    public function canEnroll(int $currentEnrolledCount): bool
    {
        if (!$this->isPublished()) {
            return false;
        }

        if ($this->maxStudents === 0) {
            // 0 bisa diartikan unlimited
            return true;
        }

        return $currentEnrolledCount < $this->maxStudents;
    }

    public function onEnroll(): void
    {
        $this->currentEnrolled++;
    }

    public function onCancelEnrollment(): void
    {
        if ($this->currentEnrolled > 0) {
            $this->currentEnrolled--;
        }
    }

    protected static function getTableName(): string
    {
        return 'courses';
    }

    protected function insert(): bool
    {
        $db = Database::getInstance()->getConnection();

        $sql = "INSERT INTO courses
                (course_code, title, description, category, max_students, current_enrolled, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $db->prepare($sql);

        $result = $stmt->execute([
            $this->courseCode,
            $this->title,
            $this->description,
            $this->category,
            $this->maxStudents,
            $this->currentEnrolled,
            $this->status,
            $this->createdAt?->format('Y-m-d H:i:s'),
        ]);

        if ($result) {
            $this->id = (int) $db->lastInsertId();
        }

        return $result;
    }

    protected function update(): bool
    {
        $db = Database::getInstance()->getConnection();

        $sql = "UPDATE courses
                SET title = ?, description = ?, category = ?, max_students = ?, current_enrolled = ?, status = ?, updated_at = ?
                WHERE id = ?";

        $stmt = $db->prepare($sql);

        return $stmt->execute([
            $this->title,
            $this->description,
            $this->category,
            $this->maxStudents,
            $this->currentEnrolled,
            $this->status,
            $this->updatedAt?->format('Y-m-d H:i:s'),
            $this->id,
        ]);
    }

    public function delete(): bool
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('DELETE FROM courses WHERE id = ?');
        return $stmt->execute([$this->id]);
    }

    public function toArray(): array
    {
        return [
            'id'               => $this->id,
            'course_code'      => $this->courseCode,
            'title'            => $this->title,
            'description'      => $this->description,
            'category'         => $this->category,
            'max_students'     => $this->maxStudents,
            'current_enrolled' => $this->currentEnrolled,
            'status'           => $this->status,
            'created_at'       => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at'       => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
