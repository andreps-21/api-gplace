@props([
    'formats' => 'jpeg,png,jpg,webp',
    'value' => asset('images/noimage.png'),
    'name' => 'image',
    'label' => 'Imagem',
    'required' => false,
    'disabled' => false,
    'readonly' => false,
])

<div class="form-group">
  <label for="inp-{{ $name }}" class="{{ $required ? 'required' : '' }}">{{ $label }}</label>
  <img src="{{ $value }}"
    class="d-block"
    id="preview-{{ $name }}"
    style= "{{ $name == 'image_post' || $name == 'photo_post_user' ? 'width: 14rem; height: 14rem;' : '' }} max-width: 100%; max-height: 100%;  border: 1px solid {{ $errors->has($name) ? 'red' : 'black' }}">
  <input type="file"
    id="inp-{{ $name }}"
    name="{{ $name }}"
    value="{{ $value }}"
    class="d-none"
    accept="image/*"
    {{ $required ? 'required' : '' }}
    {{ $disabled ? 'disabled' : '' }}
    {{ $readonly ? 'readonly' : '' }}>
  <label for="inp-{{ $name }}"
    class="btn btn-primary mt-2  {{ $disabled || $readonly ? 'd-none' : '' }}" style="padding: 9px 17px;">Trocar Imagem</label>
  @if ($errors->has($name))
    <div class="invalid-feedback"
      style="display: block">{{ $errors->first($name) }}</div>
  @endif
</div>

@push('js')
  <script>
    $("#inp-{{ $name }}").on("change", function() {
      let file = this.files[0];
      let extension = $(this).val().substring($(this).val().lastIndexOf(".") + 1).toLowerCase();
      let string = '{{ $formats }}'.replace(/\s/g, '');
      let formats = string.split(',');
      let validFormat = formats.find(element => element == extension);

      if (validFormat) {
        if (this.files && this.files[0]) {
          var reader = new FileReader();
          reader.readAsDataURL(this.files[0]);
          reader.onload = function(e) {
            $("#preview-{{ $name }}").prop("src", e.target.result);
          };
        }
        return true;
      }

      swal(
        "Atenção",
        "Formato do incompatível. Deve ser (" + string + ').',
        "warning"
      );
    });
  </script>
@endpush
