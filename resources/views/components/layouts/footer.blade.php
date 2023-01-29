@livewireScripts
@if (Auth::user()->role == 'admin')
    <script src="/js/request.js?{{ config('app.version') }}"></script>
@endif
<script src="/js/script.js?{{ config('app.version') }}" type="module"></script>
<script src="/js/notify.min.js?{{ config('app.version') }}"></script>

    <div class="scroll-to-top is-hidden" style="position:fixed;right:15px;bottom:15px;">
        <button onclick="$('html, body').animate({ scrollTop: 0 }, 'normal');" class="button is-info"><i class="fa-solid fa-angle-up"></i></button>
    </div>

    <script>
        window.addEventListener("scroll", () => { 
        if (window.pageYOffset > 100) { 
            $(".scroll-to-top").removeClass('is-hidden');
        } else { 
            $(".scroll-to-top").addClass('is-hidden');
        } 
        });
    </script>
</body>

</html>
