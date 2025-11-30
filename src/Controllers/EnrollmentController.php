<?php
// src/Controllers/EnrollmentController.php
declare(strict_types=1);

namespace App\Controllers;

use App\Builders\ApiResponseBuilder;
use App\Services\EnrollmentService;
use App\Exceptions\ValidationException;
use App\Exceptions\NotFoundException;
use App\Exceptions\EnrollmentException;

class EnrollmentController
{
    public function __construct(
        private EnrollmentService $enrollmentService,
        private ApiResponseBuilder $response
    ) {}

    // POST /enrollments
    public function create(): void
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            
            $enrollment = $this->enrollmentService->enrollStudent($input);
            $this->response->success($enrollment, 'Enrollment created successfully', 201);
            
        } catch (ValidationException $e) {
            $this->response->validationError($e->getErrors());
        } catch (EnrollmentException $e) {
            $this->response->error($e->getMessage(), 422);
        } catch (\Exception $e) {
            $this->response->error('Failed to create enrollment', 500);
        }
    }

    // GET /students/:id/enrollments
    public function getStudentEnrollments(string $id): void
    {
        try {
            $studentId = (int)$id;
            $enrollments = $this->enrollmentService->getStudentEnrollments($studentId);
            $this->response->success($enrollments, 'Student enrollments retrieved successfully');
            
        } catch (\Exception $e) {
            $this->response->error('Failed to retrieve student enrollments', 500);
        }
    }

    // PUT /enrollments/:id/complete
    public function complete(string $id): void
    {
        try {
            $enrollmentId = (int)$id;
            $enrollment = $this->enrollmentService->completeEnrollment($enrollmentId);
            $this->response->success($enrollment, 'Enrollment completed successfully');
            
        } catch (NotFoundException $e) {
            $this->response->notFound('Enrollment');
        } catch (\Exception $e) {
            $this->response->error('Failed to complete enrollment', 500);
        }
    }

    // PUT /enrollments/:id/cancel
    public function cancel(string $id): void
    {
        try {
            $enrollmentId = (int)$id;
            $enrollment = $this->enrollmentService->cancelEnrollment($enrollmentId);
            $this->response->success($enrollment, 'Enrollment cancelled successfully');
            
        } catch (NotFoundException $e) {
            $this->response->notFound('Enrollment');
        } catch (\Exception $e) {
            $this->response->error('Failed to cancel enrollment', 500);
        }
    }
}
?>