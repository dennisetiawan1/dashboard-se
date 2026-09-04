<div id="loadingSpinner" 
     style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center;">
    
    <div style="background: white; border-radius: 1rem; padding: 2rem; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
        <div style="display: flex; flex-direction: column; align-items: center; gap: 1rem;">
            <div style="width: 3rem; height: 3rem; border: 4px solid #e2e8f0; border-top-color: #2563eb; border-radius: 50%; animation: spin 1s linear infinite;"></div>
            <p style="font-size: 0.875rem; font-weight: 600; color: #374151; margin: 0;">Memproses data...</p>
        </div>
    </div>
</div>

<style>
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
</style>

<script>
    window.loadingSpinner = {
        show() {
            const spinner = document.getElementById('loadingSpinner');

            if (spinner) {
                spinner.style.display = 'flex';
            }
        },

        hide() {
            const spinner = document.getElementById('loadingSpinner');

            if (spinner) {
                spinner.style.display = 'none';
            }
        }
    };

    // HIDE LOADING SETELAH HALAMAN SELESAI DIMUAT

    window.addEventListener('load', () => {
        loadingSpinner.hide();
    });

    // LINK / NAVIGASI BIASA

    document.addEventListener('click', (e) => {

        const link = e.target.closest('a');

        if (
            link &&
            link.href &&
            !link.href.includes('#') &&
            !link.target &&
            !link.hasAttribute('download')
        ) {
            loadingSpinner.show();
        }

    });

    // FORM SUBMIT

    document.addEventListener('submit', async (e) => {

        const form = e.target;
        const submitBtn = e.submitter;

        if (!submitBtn) {
            return;
        }

        // CEK APAKAH INI TOMBOL EXPORT

        const formAction = submitBtn.getAttribute('formaction');

        const isExport =
            formAction &&
            formAction.toLowerCase().includes('export');

        // BUKAN EXPORT

        if (!isExport) {

            loadingSpinner.show();

            return;
        }

        // EXPORT

        e.preventDefault();

        loadingSpinner.show();


        try {

            // Ambil URL action dari formaction
            const action = formAction;

            // Ambil method form
            const method = (
                form.getAttribute('method') || 'GET'
            ).toUpperCase();


            // Ambil semua data form
            const formData = new FormData(form);

            let response;

            // POST

            if (method === 'POST') {

                response = await fetch(action, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

            }

            // GET

            else {

                const params = new URLSearchParams(formData);

                const separator = action.includes('?')
                    ? '&'
                    : '?';

                response = await fetch(
                    action + separator + params.toString(),
                    {
                        method: 'GET',
                        credentials: 'same-origin',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    }
                );

            }

            // CEK RESPONSE

            if (!response.ok) {
                throw new Error(
                    `Export gagal (${response.status})`
                );
            }

            // AMBIL FILE

            const blob = await response.blob();

            // AMBIL NAMA FILE

            let filename = 'export.xlsx';

            const disposition =
                response.headers.get('Content-Disposition');


            if (disposition) {

                // filename*=UTF-8''
                const utf8Match =
                    disposition.match(
                        /filename\*=UTF-8''([^;]+)/i
                    );

                // filename="..."
                const normalMatch =
                    disposition.match(
                        /filename="?([^"]+)"?/i
                    );


                if (utf8Match && utf8Match[1]) {

                    filename = decodeURIComponent(
                        utf8Match[1]
                    );

                } else if (
                    normalMatch &&
                    normalMatch[1]
                ) {

                    filename = normalMatch[1];

                }

            }

            // BUAT DOWNLOAD

            const downloadUrl =
                window.URL.createObjectURL(blob);


            const downloadLink =
                document.createElement('a');


            downloadLink.href = downloadUrl;
            downloadLink.download = filename;

            document.body.appendChild(downloadLink);

            downloadLink.click();

            downloadLink.remove();


            // Bersihkan object URL
            setTimeout(() => {
                window.URL.revokeObjectURL(downloadUrl);
            }, 1000);


        } catch (error) {

            console.error(
                'Export error:',
                error
            );

            alert(
                'Gagal melakukan export. Silakan coba lagi.'
            );

        } finally {
            // LOADING MATI SETELAH RESPONSE SELESAI
            loadingSpinner.hide();

        }

    });
</script>