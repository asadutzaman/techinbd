{{--
    Key specs editor for the product create/edit forms. Rows post as specs[n][label] / specs[n][value], in the
    order shown: the first 4 values are the key features on cards and the product page, and all of them fill
    the shop's Specification table (ProductOptimized::specs / specGroups()).
    Expects $specRows: a list of ['label' => ..., 'value' => ...].
--}}
<div class="card card-outline card-info" id="specs">
    <div class="card-header">
        <h3 class="card-title">Key specs</h3>
    </div>
    <div class="card-body">
        <p class="text-muted small">
            In order of importance: the first 4 appear on product cards and at the top of the product page ("Chip: Apple M5");
            all of them fill the Specification table. Empty rows are ignored.
        </p>
        @error('specs')
            <div class="alert alert-danger py-2">{{ $message }}</div>
        @enderror
        <div data-spec-rows>
            @foreach($specRows as $index => $row)
                <div class="form-row align-items-center mb-2" data-spec-row>
                    <div class="col-sm-4 mb-1 mb-sm-0">
                        <input type="text" class="form-control" name="specs[{{ $index }}][label]" value="{{ $row['label'] }}"
                               placeholder="Name, e.g. Processor" maxlength="60" aria-label="Spec name">
                    </div>
                    <div class="col">
                        <input type="text" class="form-control" name="specs[{{ $index }}][value]" value="{{ $row['value'] }}"
                               placeholder="Value, e.g. Intel Core i5-13420H" maxlength="255" aria-label="Spec value">
                    </div>
                    <div class="col-auto">
                        <div class="btn-group">
                            <button type="button" class="btn btn-light" data-spec-move="-1" aria-label="Move up" title="Move up"><i class="fas fa-arrow-up"></i></button>
                            <button type="button" class="btn btn-light" data-spec-move="1" aria-label="Move down" title="Move down"><i class="fas fa-arrow-down"></i></button>
                            <button type="button" class="btn btn-light text-danger" data-spec-remove aria-label="Remove" title="Remove"><i class="fas fa-times"></i></button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <button type="button" class="btn btn-sm btn-outline-info" data-spec-add><i class="fas fa-plus mr-1"></i> Add a spec</button>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var list = document.querySelector('[data-spec-rows]');
    if (!list) {
        return;
    }

    // Names follow the rows' order, so the specs save in the order shown
    function renumber() {
        list.querySelectorAll('[data-spec-row]').forEach(function (row, index) {
            row.querySelectorAll('input').forEach(function (input) {
                input.name = input.name.replace(/specs\[\d+\]/, 'specs[' + index + ']');
            });
        });
    }

    list.addEventListener('click', function (event) {
        var button = event.target.closest('button');
        var row = button && button.closest('[data-spec-row]');
        if (!row) {
            return;
        }

        if (button.hasAttribute('data-spec-remove')) {
            if (list.querySelectorAll('[data-spec-row]').length > 1) {
                row.remove();
            } else {
                row.querySelectorAll('input').forEach(function (input) { input.value = ''; });
            }
        } else if (button.dataset.specMove === '-1' && row.previousElementSibling) {
            list.insertBefore(row, row.previousElementSibling);
        } else if (button.dataset.specMove === '1' && row.nextElementSibling) {
            list.insertBefore(row.nextElementSibling, row);
        }
        renumber();
    });

    document.querySelector('[data-spec-add]').addEventListener('click', function () {
        var rows = list.querySelectorAll('[data-spec-row]');
        var row = rows[rows.length - 1].cloneNode(true);
        row.querySelectorAll('input').forEach(function (input) { input.value = ''; });
        list.appendChild(row);
        renumber();
        row.querySelector('input').focus();
    });
})();
</script>
@endpush
