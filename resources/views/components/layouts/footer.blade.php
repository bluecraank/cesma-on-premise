@livewireScripts
@if (Auth::user()->role == 'admin')
    <script src="/js/request.js?{{ config('app.version') }}"></script>
@endif
<script src="/js/script.js?{{ config('app.version') }}"></script>
</body>

</html>
