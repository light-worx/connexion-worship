<style>
    .fi-fo-table-repeater > table .fi-fo-table-repeater-actions {
        justify-content: flex-end !important;
        margin-left: auto !important;
    }
    
    /* Or alternatively, target the table cell */
    .fi-fo-table-repeater > table tbody tr td:has(.fi-fo-table-repeater-actions) {
        text-align: right;
    }
</style>