<style>

/* Target the GTranslate select menu */
.gtranslate_wrapper select {
    font-size: 9px !important;
    padding: 2px 4px !important;
    height: auto !important;
}

/* Make the flag smaller */
.gtranslate_wrapper img {
    width: 10px !important;
    height: auto !important;
    margin-right: 4px !important;
}

/* Shrink the container box */
.gtranslate_wrapper {
    border-radius: 6px !important;
    padding: 4px !important;
    border: 1px solid #0ea5e9 !important;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1) !important;
    max-width: 180px !important; /* adjust as needed */
}

/* Optional: shrink text inside */
.gtranslate_wrapper * {
    font-size: 11px !important;
    line-height: 1.2 !important;
}

      
 
 

</style>


<div class="gtranslate_wrapper"></div>
<script>
    window.gtranslateSettings = {
        default_language: "en",
        alt_flags:{"en":"usa"},
        wrapper_selector: ".gtranslate_wrapper",
        flag_style: "3d",
    };
</script>
<script src="https://cdn.gtranslate.net/widgets/latest/float.js" defer></script>
