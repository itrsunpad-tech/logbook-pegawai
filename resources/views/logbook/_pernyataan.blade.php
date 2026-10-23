<div class="lb-declaration">

    <label
        class="
            lb-checkbox
            lb-declaration-check
        "
    >

        <input
            type="checkbox"
            name="pernyataan"
            value="1"
        >

        <span>
            Saya menyatakan data yang saya isi adalah
            <strong>benar</strong>
            sesuai kegiatan yang dilakukan.
        </span>

    </label>

    @if ($jenisLama === $jenisForm && $errors->any())
        <div
            class="lb-alert lb-alert--error js-form-warning"
            data-jenis="{{ $jenisForm }}"
            role="alert"
            aria-live="assertive"
        >
            <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
            <span>
                @foreach ($errors->all() as $message)
                    <span>{{ $message }}</span>
                @endforeach
            </span>
        </div>
    @endif


    <div class="lb-submit-wrap">

        <button
            type="submit"
            class="lb-submit-button"
            @disabled(!$dibuka)
        >

            <i
                class="
                    fa-solid
                    fa-paper-plane
                "
            ></i>

            Kirim Logbook

        </button>

    </div>

</div>
