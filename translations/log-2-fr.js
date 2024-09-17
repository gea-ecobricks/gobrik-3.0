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


const fr_Page_Translations = {



//Modals for density check

    "underDensityTitle": "Kepadatan di Bawah Standar",
    "underDensityMessage": "Kepadatan ecobrick Anda sebesar ${density} berada di bawah standar GEA sebesar 0,33g/ml. Harap periksa apakah Anda telah memasukkan berat dan volume dengan benar. Jika tidak, maka mohon kemas ulang ecobrick Anda dengan lebih banyak plastik untuk mencapai kepadatan minimum. Pedoman GEA dikembangkan untuk memastikan integritas bangunan, keamanan kebakaran, dan kegunaan kembali ecobrick.",
    "lowDensityTitle": "Kepadatan Rendah",
    "lowDensityMessage": "Hati-hati! Kepadatan ecobrick Anda sebesar ${density}ml berada di sisi rendah. Ini memenuhi standar minimum sebesar 0,33g/ml, namun kepadatannya membuatnya kurang padat, kurang aman terhadap kebakaran, dan kurang dapat digunakan kembali. Teruskan dan catat ecobrick ini, tetapi lihat apakah Anda dapat mengemas lebih banyak plastik di waktu berikutnya.",
    "greatJobTitle": "Kerja bagus!",
    "greatJobMessage": "Kepadatan ecobrick Anda sebesar ${density} adalah ideal. Ini memenuhi standar minimum sebesar 0,33g/ml, membuatnya padat, aman terhadap kebakaran, dan dapat digunakan kembali.",
    "highDensityTitle": "Kepadatan Tinggi",
    "highDensityMessage": "Hati-hati, kepadatan ecobrick Anda sebesar ${density} sangat tinggi. Botol ${volume} Anda yang dikemas dengan ${weight} plastik berada di bawah kepadatan maksimum sebesar 0,73g/ml, namun kepadatan yang tinggi ini membuatnya hampir terlalu padat dan terlalu berat untuk beberapa aplikasi ecobrick. Teruskan, tetapi ingat ini untuk waktu berikutnya.",
    "overMaxDensityTitle": "Melebihi Kepadatan Maksimum",
    "overMaxDensityMessage": "Kepadatan ecobrick Anda sebesar ${density} melebihi standar GEA sebesar 0,73g/ml. Harap periksa apakah Anda telah memasukkan berat dan volume dengan benar. Jika demikian, maka mohon kemas ulang ecobrick Anda dengan lebih sedikit plastik. Pedoman GEA dikembangkan untuk memastikan keamanan dan kegunaan ecobrick untuk semua aplikasi jangka pendek dan jangka panjang.",
    "geaStandardsLinkText": "Standar GEA",
    "nextRegisterSerial": "Berikutnya: Daftar Nomor Seri",
    "goBack": "Kembali"


    };
