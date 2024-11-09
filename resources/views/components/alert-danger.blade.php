<div>
    @if($errors->any())
        <div class="alert alert-danger" style="padding: 0.5rem;border-radius: 10px">
            <ul>
                @foreach($errors->all() as $error)
                    <li style="font-size: 12px">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
