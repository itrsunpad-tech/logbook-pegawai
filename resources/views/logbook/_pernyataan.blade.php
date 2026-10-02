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