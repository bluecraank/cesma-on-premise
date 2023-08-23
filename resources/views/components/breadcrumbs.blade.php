@if(Breadcrumbs::has())
    @foreach (Breadcrumbs::current() as $crumbs)
        @if ($crumbs->url() && !$loop->last)
            <li class="breadcrumb-item breadcrumb-color">
                <a href="{{ $crumbs->url() }}">
                    {{ $crumbs->title() }}
                </a>
            </li>
        @else
            <li class="breadcrumb-item active">
                {{ $crumbs->title() }}
            </li>
        @endif
    @endforeach
@endif
