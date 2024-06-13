<html>
<head>
</head>
<body>
<div class="modal d-block margin-top-normal" tabindex="-1" role="dialog" id="modalTour">
    <div class="modal-dialog" role="document">
        <div class="modal-content rounded-6 shadow">
            <div class="modal-body p-5 center">
                <div class="d-grid gap-4 my-5">
                    در حال انتقال به درگاه پرداخت، لطفا منتظر بمانید ...
                </div>

                <form method="post" action="{{ $url }}" id="forwardForm">
                    @foreach($data as $name => $value)
                        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                    @endforeach
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('forwardForm').submit()
</script>

</body>
</html>
