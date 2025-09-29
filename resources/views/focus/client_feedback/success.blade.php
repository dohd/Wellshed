<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Submitted</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-success text-white">
            <h4 class="mb-0">Feedback Submitted Successfully</h4>
        </div>
        <div class="card-body text-center">
            <p class="fs-5">Thank you for sharing your feedback with us!</p>
            <p>Your input helps us improve our services and address your concerns effectively.</p>

            <a href="{{ route('submit-client-feedback', ['prefix' => $companyId,'uuid'=>$uuid]) }}" class="btn btn-primary">Submit New Feedback</a>
        </div>
    </div>
</div>

</body>
</html>
