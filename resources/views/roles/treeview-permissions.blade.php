@foreach ($permissions as $key => $value)
    @if (is_array($value) && !empty($value))
        @if (!$key && isset($value['name']) && !isset($closeDiv))
            @php $closeDiv = true; @endphp
            <div class="group-flex">
        @endif
        <li class="group-item my-1 mr-2">
            @if (isset($value['title']) && !empty($value['items']))
                <input type="checkbox" class="tag-all-below">
                <span class="group-title">
                    {{$value['title']}}
                    <i class="fas fa-caret-down"></i>
                </span>

                <ul class="group pl-3 border-left rounded">
                    @include('roles.treeview-permissions', ['permissions' => $value['items'], 'item' => $item ?? null])
                </ul>
            @endif
            @if (isset($value['name']))
                <input type="checkbox" name="permissions[]" value="{{$value['name']}}"
                    {{isset($item) && $item->permissions->where('name', $value['name'])->first() ? 'checked' : ''}}
                >
                <label class="permission-name m-0">{{$value['description']}}</label>
            @endif
        </li>
        @if (isset($permissions[$key+1]['title']) && isset($closeDiv))
            @php unset($closeDiv) @endphp
            </div>
        @endif
        @if (array_key_last($permissions) == $key && isset($value['name']) && isset($closeDiv))
            @php unset($closeDiv) @endphp
            </div>
        @endif
    @endif
@endforeach
