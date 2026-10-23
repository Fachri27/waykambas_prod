<script>
    // "Translate from Indonesian" buttons (partials.translateButton), see App\Http\Livewire\WithTranslation
    function cmsTranslateInput(root, name) {
        return root && root.querySelector('[wire\\:model\\.defer="' + name + '"], [wire\\:model="' + name + '"]');
    }

    function cmsTranslateText(root, editor, name) {
        const input = editor ? null : cmsTranslateInput(root, name);
        return (editor ? editor.getContent({ format: 'text' }) : (input ? input.value : '')).trim().toLowerCase();
    }

    function cmsTranslate($wire, field, button) {
        const root = button.closest('[wire\\:id]');
        const source = window.tinymce && tinymce.get(field + 'ID');
        const current = cmsTranslateText(root, window.tinymce && tinymce.get(field + 'EN'), field + 'EN');
        // only ask when there is real English text, not empty or a copy of the Indonesian
        if (current !== '' && current !== cmsTranslateText(root, source, field + 'ID') && !confirm('Replace the current English text with a new translation?')) {
            return Promise.resolve();
        }
        // rich text editors only sync to livewire on blur, so send their content along
        return $wire.translateToEnglish(field, source ? source.getContent() : null);
    }

    window.addEventListener('translated', function (e) {
        const editor = window.tinymce && tinymce.get(e.detail.field);
        if (editor) {
            // a single undo step, so ctrl+z brings the previous text back
            editor.undoManager.transact(function () {
                editor.setContent(e.detail.value);
            });
            return;
        }
        // livewire already filled the input, refresh its character counter
        const input = cmsTranslateInput(document, e.detail.field);
        if (input) {
            input.dispatchEvent(new Event('keyup'));
        }
    });
</script>
