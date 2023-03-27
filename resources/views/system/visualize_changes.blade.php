<div>
    <button class="button is-link is-small" 
    onclick='
    $(".is-changes-modal").addClass("is-active");
    $(".changes").html(prettyPrintJson.toHtml(
    {{  $diff }}
    ));
    $(".topic").html("{{  $log->category }}");
    $(".value").html("{{  $log->level }}");
    $(".date").html("{{ $log->created_at->format('Y-m-d H:i:s') }}");
    '>See changes</button>
</div>