<?php
require_once __DIR__ . '/../layouts/admin_header.php';

// Fee management only: list document types with their current fees.
// No creation, deletion, renaming, or activation changes on this page.
$documents = $auth->getRows("SELECT document_id, document_name, description, processing_days, fee, active FROM document_types ORDER BY document_name ASC");
?>

<div class="max-w-7xl mx-auto">
    <header class="mb-12">
        <div>
            <h1 class="text-4xl md:text-5xl font-black uppercase tracking-tighter mb-2">Document Fees</h1>
            <p class="text-lg font-bold text-gray-600 uppercase tracking-wide">View and update the fee charged per document. Student payment requests use these amounts.</p>
        </div>
    </header>

    <div class="bg-white border-4 border-black shadow-brutal-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-black text-white uppercase text-xs font-black tracking-widest">
                        <th class="p-4 border-r-2 border-gray-800">Document</th>
                        <th class="p-4 border-r-2 border-gray-800">Description</th>
                        <th class="p-4 border-r-2 border-gray-800 text-center">Processing Days</th>
                        <th class="p-4 border-r-2 border-gray-800 text-right">Fee (₱)</th>
                        <th class="p-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-black">
                    <?php if (empty($documents)): ?>
                        <tr>
                            <td colspan="5" class="p-12 text-center font-bold text-gray-500 uppercase">No document types found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($documents as $doc): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="p-4 border-r-2 border-black font-bold text-sm"><?= htmlspecialchars($doc['document_name']); ?></td>
                                <td class="p-4 border-r-2 border-black text-sm font-medium"><?= htmlspecialchars($doc['description'] ?? 'No description available.'); ?></td>
                                <td class="p-4 border-r-2 border-black text-sm font-medium text-center"><?= htmlspecialchars($doc['processing_days']); ?></td>
                                <td class="p-4 border-r-2 border-black text-right">
                                    <form class="form-fee flex items-center justify-end gap-2">
                                        <input type="hidden" name="document_id" value="<?= (int)$doc['document_id']; ?>">
                                        <input type="hidden" name="csrf_token" value="<?= $auth->generateCsrfToken(); ?>">
                                        <span class="font-black">₱</span>
                                        <input type="number" name="fee" class="w-32 border-2 border-black p-2 rounded-none focus:outline-none focus:ring-2 focus:ring-black font-bold text-right" min="0" max="99999999.99" step="0.01" value="<?= htmlspecialchars(number_format((float)$doc['fee'], 2, '.', '')); ?>" required aria-label="Fee for <?= htmlspecialchars($doc['document_name']); ?>">
                                        <button type="submit" class="px-3 py-2 border-2 border-black bg-brutal-yellow font-black text-[10px] uppercase shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all">
                                            Save
                                        </button>
                                    </form>
                                </td>
                                <td class="p-4 text-right text-sm font-medium text-gray-500">
                                    <?= $doc['active'] ? 'Active' : 'Inactive'; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/admin_footer.php'; ?>
<script>
$(document).on('submit', '.form-fee', function(e) {
    e.preventDefault();
    var formData = $(this).serialize();
    var submitBtn = $(this).find('button[type="submit"]');

    submitBtn.prop('disabled', true).text('Saving...');

    $.ajax({
        url: 'update_document_fee.php',
        type: 'POST',
        dataType: 'json',
        data: formData,
        success: function(data) {
            if (data.valid) {
                alert(data.msg);
                location.reload();
            } else {
                alert(data.msg);
                submitBtn.prop('disabled', false).text('Save');
            }
        },
        error: function() {
            alert('An error occurred. Please try again.');
            submitBtn.prop('disabled', false).text('Save');
        }
    });
});
</script>
