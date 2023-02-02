    <div class="scroll-to-top is-hidden" style="position:fixed;right:15px;bottom:15px;">
        <button onclick="$('html, body').animate({ scrollTop: 0 }, 'normal');" class="button is-info"><i
                class="fa-solid fa-angle-up"></i></button>
    </div>
    </body>
    
    @if (Auth::user()->role >= 1)
        <script src="/js/request.js?{{ config('app.version') }}"></script>
    @endif

    </html>
