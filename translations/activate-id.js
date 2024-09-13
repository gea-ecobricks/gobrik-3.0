/*-----------------------------------
TEXT TRANSLATION SNIPPETS FOR GOBRIK.com
-----------------------------------*/

// Ampersand (&): Should be escaped as &amp; because it starts HTML character references.
// Less-than (<): Should be escaped as &lt; because it starts an HTML tag.
// Greater-than (>): Should be escaped as &gt; because it ends an HTML tag.
// Double quote ("): Should be escaped as &quot; when inside attribute values.
// Single quote/apostrophe ('): Should be escaped as &#39; or &apos; when inside attribute values.
// Backslash (\): Should be escaped as \\ in JavaScript strings to prevent ending the string prematurely.
// Forward slash (/): Should be escaped as \/ in </script> tags to prevent prematurely closing a script.

const id_Page_Translations = {

    // Activate
    "0001-activate-notice": "Sejak terakhir kali Anda masuk, kami telah melakukan peningkatan besar pada GoBrik.",
    "0002-activate-explantion-1": "Versi lama GoBrik kami berjalan di server dan kode perusahaan. Kami telah membiarkannya berlalu.",
    "0002-activate-explantion-2": "Sebagai gantinya, kami telah memigrasi semua data kami ke server mandiri yang independen. GoBrik 3.0 baru kami sekarang 100% sumber terbuka dan sepenuhnya berfokus pada akuntabilitas ekologis. Kami juga telah mengembangkan sistem login Buwana kami sendiri sebagai alternatif untuk login Google dan Apple. Untuk bergabung dengan kami di GoBrik yang diregenerasi dengan ",
    "0002-activate-explantion-3": " harap luangkan waktu sebentar untuk meningkatkan ke akun Buwana.",

    // Activate 2
    "001-set-your-pass": "Atur Kata Sandi Baru Anda",
    "002-to-get-going": " Untuk memulai dengan akun yang ditingkatkan, silakan atur kata sandi baru...",
    "007-set-your-pass": "Atur kata sandi Anda:",
    "008-password-advice": "🔑 Kata sandi Anda harus memiliki setidaknya 6 karakter.",
    "009-confirm-pass": "Konfirmasi Kata Sandi Anda:",
    "010-pass-error-no-match": "👉 Kata sandi tidak cocok.",
    "013-by-registering": "Dengan mendaftar hari ini, saya setuju dengan &lt;a href=&quot;#&quot; onclick=&quot;showModalInfo(&#39;terms&#39;)&quot; class=&quot;underline-link&quot;&gt;Ketentuan Layanan GoBrik&lt;/a&gt;",
    "014-i-agree-newsletter": "Tolong kirimkan saya &lt;a href=&quot;#&quot; onclick=&quot;showModalInfo(&#39;earthen&#39;, &#39;id&#39;)&quot; class=&quot;underline-link&quot;&gt;newsletter Earthen&lt;/a&gt; untuk pembaruan aplikasi, ecobrick, dan earthen",
    "015-confirm-pass-button": "&lt;input type=&quot;submit&quot; id=&quot;submit-button&quot; value=&quot;Konfirmasi Kata Sandi&quot; class=&quot;submit-button disabled&quot;&gt;",

    // Confirm email
    "0003-activate-button": "&lt;input type=&quot;submit&quot; id=&quot;submit-button&quot; value=&quot;🍃 Tingkatkan Akun!&quot; class=&quot;submit-button activate&quot;&gt;",
    "0004-buwana-accounts": "Akun Buwana dirancang dengan mempertimbangkan ekologi, keamanan, dan privasi. Segera, Anda dapat masuk ke aplikasi regeneratif hebat lainnya dengan cara yang sama seperti Anda masuk ke GoBrik!",
    "0005-new-terms": "Syarat dan Ketentuan Baru Buwana &amp; GoBrik",
    "0005-regen-blog": "Mengapa? Baca posting blog kami &#39;Regenerasi Besar GoBrik&#39;.",
    "0006-github-code": "Repositori Kode Sumber Github Baru",
    "0007-not-interested": "Jika Anda tidak tertarik dan ingin ",
    "0009-that-too": " akun lama Anda sepenuhnya dihapus, Anda juga dapat melakukannya.",
    "0010-delete-button": "Hapus Akun Saya",
    "0011-warning": "PERINGATAN: Ini tidak dapat dibatalkan.",
    "001-alright": "Baiklah",
    "002-lets-confirm": "mari konfirmasi email Anda.",
    "003-to-create": "Untuk membuat akun Buwana GoBrik Anda, kami perlu mengonfirmasi kredensial yang Anda pilih. Ini adalah cara kami akan tetap berhubungan dan menjaga keamanan akun Anda. Klik tombol kirim dan kami akan mengirimkan kode aktivasi akun ke:",
    "004-send-email-button": '<input type="submit" name="send_email" id="send_email" value="📨 Kirim Kode" class="submit-button activate">',
    "006-enter-code": "Silakan masukkan kode Anda:",
    "007-check-email": "Periksa email Anda",
    "008-for-your-code": "untuk kode konfirmasi akun Anda. Masukkan di sini:",
    "009-no-code": "Tidak menerima kode Anda? Anda dapat meminta pengiriman ulang kode dalam",
    "010-email-no-longer": "Apakah Anda tidak lagi menggunakan alamat email ini?&lt;br&gt;Jika tidak, Anda perlu &lt;a href=&quot;signup.php&quot;&gt;membuat akun baru&lt;/a&gt; atau hubungi tim kami di support@gobrik.com.",
    "011-change-email": "Ingin mengganti email Anda?",
    "012-go-back-new-email": "Kembali untuk memasukkan alamat email yang berbeda.",

    // Activate-3.php
    "012-status-heading": "kata sandi Anda telah diatur! Sekarang mari kita tentukan lokasi Anda.",
    "013-sub-status-tell": "Akun Buwana dan GoBrik baru Anda berfokus pada aksi ekologi lokal dan global. Tolong beri tahu kami di mana Anda tinggal...",
    "014-your-continent": "Di benua mana Anda tinggal?",
    "015-continent-place-holder": "Pilih benua Anda...",
    "014-your-country": "Di negara mana Anda tinggal?",
    "015-country-place-holder": "Pilih negara tempat tinggal Anda...",
    "014-your-watershed": "Di daerah aliran sungai mana Anda tinggal?",
    "015-watershed-place-holder": "Pilih daerah aliran sungai Anda...",
    "016-dont-know": "Saya tidak yakin",
    "016-dont-know-alt": "Tidak terdaftar",
    "018-what-is-watershed": "Hampir semua orang tinggal di salah satu dari 200 daerah aliran sungai utama di Bumi. Lihat apakah Anda dapat menemukan milik Anda! Pelajari lebih lanjut tentang ",
    "019-watershed": "daerah aliran sungai",
    "016-submit-complete-button": '<input type="submit" id="submit-button" value="Selesaikan Pengaturan" class="submit-button enabled"><p style="font-size:smaller;">Tidak menemukan daerah aliran sungai Anda? Jangan khawatir! Kami masih bekerja untuk menambahkannya.</p>',

        "015-continents-caption": "Benua adalah tempat bioma, ekosistem utama Bumi, mendapatkan keanekaragaman dan vitalitas spesies mereka yang unik.",
    "0016-select-country-placeholder": "Pilih negara tempat tinggal Anda...",

};

