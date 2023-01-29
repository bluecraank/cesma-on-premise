@livewireScripts
@if (Auth::user()->role == 'admin')
    <script src="/js/request.js?{{ config('app.version') }}"></script>
@endif
<script src="/js/script.js?{{ config('app.version') }}" type="module"></script>
<script src="/js/notify.min.js?{{ config('app.version') }}"></script>
<script>

</script>
</body>

</html>
