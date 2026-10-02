@push('scripts')

<script>
(() => {

    const DATA =
        @json($kalender ?? []);

    const LIBUR =
        @json($libur ?? []);

    const HARI_INI =
        @json($hariIni);

    const DIBUKA =
        @json($dibuka);


    const NAMA = {
        harian: 'Logbook Harian',
        lembur: 'Lembur',
        oncall: 'On Call',
    };


    const pad = number =>
        String(number).padStart(2, '0');


    const makeKey = (
        year,
        month,
        day
    ) =>
        `${year}-${pad(month)}-${pad(day)}`;


    const grid =
        document.getElementById(
            'kal-grid'
        );

    const judul =
        document.getElementById(
            'kal-judul'
        );

    const prev =
        document.getElementById(
            'kal-prev'
        );

    const next =
        document.getElementById(
            'kal-next'
        );


    /*
     * Pastikan elemen kalender
     * benar-benar tersedia.
     */
    if (
        !grid ||
        !judul ||
        !prev ||
        !next
    ) {
        return;
    }


    /*
     * Hari ini.
     */
    const [
        tahunHariIni,
        bulanHariIni
    ] =
        HARI_INI
            .split('-')
            .map(Number);


    /*
     * Hanya dua bulan:
     * bulan lalu + bulan sekarang.
     */
    const daftarBulan = [

        bulanHariIni === 1
            ? {
                year: tahunHariIni - 1,
                month: 12
            }
            : {
                year: tahunHariIni,
                month: bulanHariIni - 1
            },

        {
            year: tahunHariIni,
            month: bulanHariIni
        }

    ];


    let bulanAktif = 1;


    function render() {

        const {
            year,
            month
        } =
            daftarBulan[
                bulanAktif
            ];


        /*
         * Judul bulan.
         */
        judul.textContent =
            new Date(
                year,
                month - 1,
                1
            ).toLocaleDateString(
                'id-ID',
                {
                    month: 'long',
                    year: 'numeric'
                }
            );


        /*
         * Navigasi hanya dua bulan.
         */
        prev.disabled =
            bulanAktif === 0;

        next.disabled =
            bulanAktif ===
            daftarBulan.length - 1;


        grid.innerHTML = '';


        /*
         * Hari pertama.
         *
         * 0 = Minggu
         */
        const hariPertama =
            new Date(
                year,
                month - 1,
                1
            ).getDay();


        /*
         * Kotak kosong.
         */
        for (
            let i = 0;
            i < hariPertama;
            i++
        ) {

            const empty =
                document.createElement(
                    'div'
                );

            empty.className =
                'lb-cal-empty';

            grid.appendChild(
                empty
            );

        }


        /*
         * Jumlah hari dalam bulan.
         */
        const jumlahHari =
            new Date(
                year,
                month,
                0
            ).getDate();


        /*
         * Render tanggal.
         */
        for (
            let day = 1;
            day <= jumlahHari;
            day++
        ) {

            const tanggal =
                makeKey(
                    year,
                    month,
                    day
                );


            const hariIni =
                tanggal ===
                HARI_INI;


            const masaDepan =
                tanggal >
                HARI_INI;


            const button =
                document.createElement(
                    'button'
                );


            button.type =
                'button';


            button.className =
                'lb-cal-day' +
                (
                    hariIni
                        ? ' is-today'
                        : ''
                );


            button.disabled =
                masaDepan;


            const nomor =
                document.createElement(
                    'span'
                );


            nomor.textContent =
                String(day);


            button.appendChild(
                nomor
            );


            /*
             * Aktivitas logbook.
             */
            const jenis =
                new Set(
                    (
                        DATA[tanggal] ||
                        []
                    ).map(
                        item =>
                            item.jenis
                    )
                );


            /*
             * Libur nasional.
             */
            if (
                LIBUR[tanggal]
            ) {

                jenis.add(
                    'libur'
                );

            }


            /*
             * Titik indikator.
             */
            const dots =
                document.createElement(
                    'span'
                );


            dots.className =
                'lb-dots';


            jenis.forEach(
                namaJenis => {

                    const dot =
                        document.createElement(
                            'i'
                        );


                    dot.className =
                        `lb-dot lb-dot--${namaJenis}`;


                    dots.appendChild(
                        dot
                    );

                }
            );


            button.appendChild(
                dots
            );


            /*
             * Klik tanggal.
             */
            button.addEventListener(
                'click',
                () =>
                    bukaModal(
                        tanggal
                    )
            );


            grid.appendChild(
                button
            );

        }

    }


    /*
     * Sebelumnya.
     */
    prev.addEventListener(
        'click',
        () => {

            bulanAktif = 0;

            render();

        }
    );


    /*
     * Berikutnya.
     */
    next.addEventListener(
        'click',
        () => {

            bulanAktif = 1;

            render();

        }
    );


    /* =====================================
       MODAL
    ===================================== */

    const modal =
        document.getElementById(
            'kal-modal'
        );

    const modalJudul =
        document.getElementById(
            'kal-modal-judul'
        );

    const modalSub =
        document.getElementById(
            'kal-modal-sub'
        );

    const modalList =
        document.getElementById(
            'kal-modal-list'
        );

    const tombolTutup =
        document.getElementById(
            'kal-modal-tutup'
        );

    const tombolIsi =
        document.getElementById(
            'kal-modal-isi'
        );


    let tanggalDipilih =
        null;


    function formatTanggal(
        tanggal
    ) {

        const [
            year,
            month,
            day
        ] =
            tanggal
                .split('-')
                .map(Number);


        return new Date(
            year,
            month - 1,
            day
        ).toLocaleDateString(
            'id-ID',
            {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            }
        );

    }


    function bukaModal(
        tanggal
    ) {

        tanggalDipilih =
            tanggal;


        const items =
            DATA[tanggal] ||
            [];


        modalJudul.textContent =
            formatTanggal(
                tanggal
            );


        modalSub.textContent =
            items.length
                ? `${items.length} logbook terisi`
                : 'Belum ada logbook terisi';


        modalList.innerHTML = '';


        /*
         * Libur nasional.
         */
        if (
            LIBUR[tanggal]
        ) {

            const li =
                document.createElement(
                    'li'
                );


            li.className =
                'lb-modal-item';


            const dot =
                document.createElement(
                    'i'
                );


            dot.className =
                'lb-dot lb-dot--libur';


            li.appendChild(
                dot
            );


            const text =
                document.createElement(
                    'span'
                );


            text.textContent =
                `Libur: ${LIBUR[tanggal]}`;


            li.appendChild(
                text
            );


            modalList.appendChild(
                li
            );

        }


        /*
         * Logbook.
         */
        items.forEach(
            item => {

                const li =
                    document.createElement(
                        'li'
                    );


                li.className =
                    'lb-modal-item';


                const dot =
                    document.createElement(
                        'i'
                    );


                dot.className =
                    `lb-dot lb-dot--${item.jenis}`;


                li.appendChild(
                    dot
                );


                const text =
                    document.createElement(
                        'span'
                    );


                text.textContent =
                    NAMA[item.jenis] ??
                    item.jenis;


                if (
                    item.detail
                ) {

                    const small =
                        document.createElement(
                            'small'
                        );


                    small.textContent =
                        item.detail;


                    text.appendChild(
                        small
                    );

                }


                li.appendChild(
                    text
                );


                modalList.appendChild(
                    li
                );

            }
        );


        /*
         * Kosong.
         */
        if (
            !items.length &&
            !LIBUR[tanggal]
        ) {

            const li =
                document.createElement(
                    'li'
                );


            li.className =
                'lb-modal-item lb-modal-item--empty';


            li.textContent =
                'Belum ada aktivitas pada tanggal ini.';


            modalList.appendChild(
                li
            );

        }


        /*
         * Tombol isi hanya jika:
         * tanggal <= hari ini
         * dan pengisian masih dibuka.
         */
        const bolehIsi =
            tanggal <= HARI_INI &&
            DIBUKA;


        tombolIsi.classList.toggle(
            'is-hidden',
            !bolehIsi
        );


        modal.classList.add(
            'is-open'
        );

    }


    function tutupModal() {

        modal.classList.remove(
            'is-open'
        );

    }


    tombolTutup.addEventListener(
        'click',
        tutupModal
    );


    modal.addEventListener(
        'click',
        event => {

            if (
                event.target ===
                modal
            ) {

                tutupModal();

            }

        }
    );


    document.addEventListener(
        'keydown',
        event => {

            if (
                event.key ===
                'Escape'
            ) {

                tutupModal();

            }

        }
    );


    tombolIsi.addEventListener(
        'click',
        () => {

            document
                .querySelectorAll(
                    'input[name="tanggal"]'
                )
                .forEach(
                    input => {

                        input.value =
                            tanggalDipilih;

                        input.dispatchEvent(
                            new Event(
                                'change'
                            )
                        );

                    }
                );


            tutupModal();


            const formAktif =
                document.querySelector(
                    '.lb-form.is-active'
                );


            if (
                formAktif
            ) {

                formAktif.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });

            }

        }
    );


    /*
     * Jalankan pertama kali.
     */
    render();

})();
</script>

@endpush