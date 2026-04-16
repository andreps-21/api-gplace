<div class="modal fade" id="emailmodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">

        <div class="modal-body">
            @csrf
            <h4 class="modal-title" id="myModalLabel" style="font-weight: 900">Recuperação de senha.</h4>
            <br />
            <h6 class="login-heading mb-4" style="font-weight: 500">Informe o seu e-mail cadastrado para receber o código de recuperação de acesso.</h6>
            <div class="form-group">
              <label for="inputEmailModal">Email Cadastrado</label>
              <input type="email" name="email" id="inputEmailModal" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" required autofocus>
              <br />
              <div class="alert alert-danger" id="error-message-email" style="display: none;"></div>
            </div>

            <div class="d-flex justify-content-center">
              <div class="justify-content-center">
                <button class="btn btn-simple btn-cancel-modal ml-2" data-dismiss="modal">CANCELAR</button>
                <button class="btn btn-card btn-send-modal ml-2" id="submitModal">ENVIAR</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    $(document).ready(function() {

      $('#submitModal').click(function() {
        var email = $('#inputEmailModal').val();
        var csrfToken = $('#emailmodal input[name="_token"]').val();
        if (email.trim() !== '') {
          var formData = {
            email: email,
            _token: csrfToken
          };
          $.ajax({
            type: 'POST',
            url: '{{ route("password.email") }}',
            data: formData,
            success: function(response) {
              if (response.error) {
                $('#error-message-email').text(response.error);
                $('#error-message-email').show();
              } else {
                $('#emailmodal').modal('hide');
                $('#codemodal').modal('show');
              }
            },
          });
        } else {
          $('#error-message-email').text('Por favor, preencha o campo de e-mail.');
          $('#error-message-email').show();
        }
      });
    });
  </script>
