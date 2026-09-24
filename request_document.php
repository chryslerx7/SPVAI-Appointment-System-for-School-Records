<?php
require_once('layouts/student_header.php');

// Fetch active document types
$sql = "SELECT * FROM document_types WHERE active = 1 ORDER BY document_name ASC";
$documents = $auth->getRows($sql);
?>

<div class="max-w-6xl mx-auto">
    <header class="mb-12">
        <h1 class="text-4xl md:text-5xl font-black uppercase tracking-tighter mb-2">Request Document</h1>
        <p class="text-lg font-bold text-gray-600 uppercase tracking-wide">Select a document from our official list to start your request.</p>
    </header>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php foreach($documents as $doc): ?>
            <div class="bg-white border-4 border-black shadow-brutal-lg p-6 flex flex-col">
                <div class="mb-4">
                    <h3 class="text-xl font-black uppercase mb-2"><?= htmlspecialchars($doc['document_name']); ?></h3>
                    <p class="text-sm text-gray-600 mb-4"><?= htmlspecialchars($doc['description'] ?? 'No description available.'); ?></p>
                </div>

                <div class="mt-auto space-y-2 mb-6">
                    <div class="flex justify-between text-xs font-black uppercase">
                        <span>Processing Time:</span>
                        <span><?= htmlspecialchars($doc['processing_days']); ?> Days</span>
                    </div>
                    <div class="flex justify-between text-xs font-black uppercase">
                        <span>Fee:</span>
                        <span class="text-lg font-black">₱<?= number_format($doc['fee'], 2); ?></span>
                    </div>
                </div>

                <button class="btn-request w-full bg-brutal-yellow border-2 border-black py-3 font-black uppercase tracking-wide shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all"
                        data-id="<?= $doc['document_id']; ?>"
                        data-name="<?= htmlspecialchars($doc['document_name']); ?>">
                    Request Now
                </button>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Brutalist Modal -->
<div id="modal-request" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50 backdrop-blur-sm">
    <div class="bg-white border-4 border-black shadow-brutal-lg w-full max-w-md overflow-hidden">
        <div class="p-6 border-b-4 border-black flex justify-between items-center bg-brutal-yellow">
            <h4 class="text-xl font-black uppercase tracking-tighter">Request: <span id="doc-name-display" class="text-black"></span></h4>
            <button class="close-modal text-2xl font-black leading-none hover:text-red-500">&times;</button>
        </div>
        <form id="form-request" class="p-6 space-y-6">
            <input type="hidden" name="document_id" id="input-doc-id">
            <input type="hidden" name="csrf_token" value="<?= $auth->generateCsrfToken(); ?>">

            <div class="space-y-1">
                <label class="block text-xs font-black uppercase">Purpose of Request</label>
                <textarea name="purpose" id="purpose" class="w-full border-2 border-black p-3 rounded-none focus:outline-none focus:ring-2 focus:ring-black" rows="3" required placeholder="e.g., Scholarship application, Employment, etc."></textarea>
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-black uppercase">Number of Copies</label>
                <input type="number" name="copies" id="copies" class="w-full border-2 border-black p-3 rounded-none focus:outline-none focus:ring-2 focus:ring-black" min="1" max="10" value="1" required>
                <p class="text-[10px] font-bold text-gray-500 uppercase">Maximum 10 copies per request.</p>
            </div>

            <div class="flex gap-4">
                <button type="button" class="close-modal flex-1 py-3 border-2 border-black font-black uppercase text-sm shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all">
                    Cancel
                </button>
                <button type="submit" class="flex-1 py-3 bg-brutal-yellow border-2 border-black font-black uppercase text-sm shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all">
                    Submit Request
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once('layouts/student_footer.php'); ?>
<script>
$(document).on('click', '.btn-request', function() {
    var docId = $(this).data('id');
    var docName = $(this).data('name');

    $('#input-doc-id').val(docId);
    $('#doc-name-display').text(docName);
    $('#modal-request').removeClass('hidden');
});

$(document).on('click', '.close-modal', function() {
    $('#modal-request').addClass('hidden');
});

$(document).on('submit', '#form-request', function(e) {
    e.preventDefault();
    var formData = $(this).serialize();
    var submitBtn = $(this).find('button[type="submit"]');

    submitBtn.prop('disabled', true).text('Submitting...');

    $.ajax({
        url: 'data/create_request.php',
        type: 'POST',
        dataType: 'json',
        data: formData,
        success: function(data) {
            if (data.valid) {
                alert(data.msg);
                window.location = data.url;
            } else {
                alert(data.msg);
                submitBtn.prop('disabled', false).text('Submit Request');
            }
        },
        error: function() {
            alert('An error occurred. Please try again.');
            submitBtn.prop('disabled', false).text('Submit Request');
        }
    });
});
</script>
