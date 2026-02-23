</div>
</div>

<footer class="footer px-4 mt-4" style="background-color: transparent">
    <div>
        Outil développé par Tom MARTIN.
    </div>
</footer>
</div>
<?php if (isset($messages)) { ?>

    <script type="text/javascript">
        jQuery(function(){
            toastr.options = {
                "closeButton": true,
                "progressBar": true,
                "onclick": null,
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut",
                "newestOnTop": true,
            };

            var messages = <?= json_encode($messages) ?>;
            var delay = 750; // 500 milliseconds delay between each toast

            messages.forEach(function(elem, index){
                setTimeout(function(){
                    toastr[elem.toast](elem.txt);
                }, index * delay);
            });
        });
    </script>
<?php } ?>
</body>
</html>
