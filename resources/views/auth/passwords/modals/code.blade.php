<div class="modal fade" id="codemodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">

        <div class="modal-body">

          <h4 class="modal-title" id="myModalLabel" style="font-weight: 900">Informe o Código.</h4>
          <br />
          <h6 class="login-heading mb-4" style="font-weight: 500">Para prosseguir, informe o código de verificação que recebeu.</h6>
          <div class="form-group">
            <label for="inputCodelModal">Código</label>
            <input type="email" name="email" id="inputCodelModal" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" required autofocus>
            <br />
            <div class="alert alert-danger" id="error-message-code" style="display: none;"></div>
          </div>
          <div class="d-flex justify-content-center">
            <div class="justify-content-center">
              <button class="btn btn-simple btn-cancel-modal ml-2" data-dismiss="modal">CANCELAR</button>
              <button class="btn btn-card btn-send-modal ml-2" id="codeSend">CONFIRMAR</button>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>


  <script>
    $(document).ready(function() {

      $('#codeSend').click(function() {
        var code = $('#inputCodelModal').val();
        var csrfToken = $('#emailmodal input[name="_token"]').val();
        if (code.trim() !== '') {
          var formData = {
            code: code,
            _token: csrfToken
          };
          $.ajax({
            type: 'POST',
            url: "{{ route('password.check-code')}}",
            data: formData,
            success: function(response) {
              if (response.error) {
                $('#error-message-code').text(response.error);
                $('#error-message-code').show();
              } else {
                $('#codemodal').modal('hide');
                $('#passwordmodal').modal('show');
              }
            },
            error: function(xhr, status, error) {
              $('#error-message-code').text(error);
              $('#error-message-code').show();
            }
          });
        } else {
          $('#error-message-code').text('Por favor, preencha o campo de código.');
          $('#error-message-code').show();
        }
      });
    });
  </script>
