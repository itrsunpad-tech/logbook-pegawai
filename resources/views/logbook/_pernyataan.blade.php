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

    @if (
        in_array($jenisLama, ['lembur', 'oncall'], true)
        && $errors->has('jam_selesai')
        && str_contains($errors->first('jam_selesai'), 'belum memenuhi durasi minimal')
    )
        <div
            class="lb-alert lb-alert--error js-duration-error"
            data-jenis="{{ $jenisLama }}"
            role="alert"
            aria-live="assertive"
        >
            {{ $errors->first('jam_selesai') }}
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
