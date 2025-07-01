<?php

namespace Iberbanco\SDK\DTOs\Export;

use Iberbanco\SDK\DTOs\BaseDTO;
use Iberbanco\SDK\Exceptions\ValidationException;

class ExportDataDTO extends BaseDTO
{
    public ?string $format = null;
    public ?string $date_from = null;
    public ?string $date_to = null;
    public ?int $limit = null;
    public ?array $columns = null;
    public ?string $notify_email = null;
    public ?bool $compressed = null;

    public function validate(): void
    {
        if ($this->format && !in_array(strtolower($this->format), ['csv', 'xlsx', 'json', 'xml'])) {
            throw ValidationException::invalidValue('format', $this->format, ['csv', 'xlsx', 'json', 'xml']);
        }

        if ($this->limit !== null && ($this->limit < 1 || $this->limit > 100000)) {
            throw ValidationException::invalidValue('limit', $this->limit, ['1-100000']);
        }

        if ($this->notify_email && !filter_var($this->notify_email, FILTER_VALIDATE_EMAIL)) {
            throw ValidationException::invalidEmail($this->notify_email);
        }

        if ($this->date_from) {
            $this->validateDate($this->date_from, 'date_from');
        }

        if ($this->date_to) {
            $this->validateDate($this->date_to, 'date_to');
        }

        if ($this->date_from && $this->date_to) {
            $dateFrom = new \DateTime($this->date_from);
            $dateTo = new \DateTime($this->date_to);
            
            if ($dateFrom > $dateTo) {
                throw ValidationException::invalidValue(
                    'date_range',
                    $this->date_from . ' - ' . $this->date_to,
                    ['date_from must be before date_to']
                );
            }

            $maxRange = 365;
            $diff = $dateTo->diff($dateFrom)->days;
            
            if ($diff > $maxRange) {
                throw ValidationException::invalidValue(
                    'date_range',
                    $this->date_from . ' - ' . $this->date_to,
                    ["Date range cannot exceed {$maxRange} days"]
                );
            }
        }
    }

    public function getRequiredFields(): array
    {
        return []; // No required fields for export
    }
} 