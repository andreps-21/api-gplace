<div class="modal fade" id="passwordmodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">

        <div class="modal-body">
          <h4 class="modal-title" id="myModalLabel" style="font-weight: 900">Informe a nova senha!</h4>
          <br />
          <h6 class="login-heading mb-4" style="font-weight: 500">Para concluir, informe uma nova senha e confirme.</h6>
          <form>

            <label>Nova senha</label>
            <div class="input-group mb-3">
              <input type="password" id="password_modal" name="password_modal" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" required>
              <div class="input-group-append">
                <span class="input-group-text olho_modal btn btn-default" id="olho_modal">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
                    <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z" />
                    <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z" />
                  </svg>
                </span>
              </div>
            </div>

            <label>Confirmar senha</label>
            <div class="input-group mb-3">
              <input type="password" id="password_modal_confirmed" name="password_modal_confirmed" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" required>
              <div class="input-group-append">
                <span class="input-group-text olho_modal_confirmed btn btn-default" id="olho_modal_confirmed">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
                    <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z" />
                    <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z" />
                  </svg>
                </span>
              </div>
            </div>
            <br />
            <div class="alert alert-danger" id="error-message-password" style="display: none;"></div>
            <div class="alert alert-success" id="sucess-message-password" style="display: none;"></div>
          </form>

          <div class="d-flex justify-content-center">
            <div class="justify-content-center">
              <button class="btn btn-simple btn-cancel-modal ml-2" data-dismiss="modal">CANCELAR</button>
              <button class="btn btn-card btn-send-modal ml-2" id="submitPassword">CONFIRMAR</button>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>


  <script>
    document.getElementById('olho_modal').addEventListener('mousedown', function() {
      document.getElementById('password_modal').type = 'text';
    });
    document.getElementById('olho_modal_confirmed').addEventListener('mousedown', function() {
      document.getElementById('password_modal_confirmed').type = 'text';
    });
    document.getElementById('olho_modal').addEventListener('mouseup', function() {
      document.getElementById('password_modal').type = 'password';
    });
    document.getElementById('olho_modal_confirmed').addEventListener('mouseup', function() {
      document.getElementById('password_modal_confirmed').type = 'password';
    });


    $(document).ready(function() {

      $('#submitPassword').click(function() {
        var code = $('#inputCodelModal').val();
        var password = $('#password_modal').val();
        var password_confirmation = $('#password_modal_confirmed').val();
        var csrfToken = $('#emailmodal input[name="_token"]').val();
        var formData = {
          code: code,
          password: password,
          password_confirmation: password_confirmation,
          _token: csrfToken
        };
        if (password === '' || password_confirmation === '') {
          $('#error-message-password').text("Por favor, preencha ambos os campos de senha.");
          $('#error-message-password').show();
        } else if (password.length < 6 || password_confirmation.length < 6) {
          $('#error-message-password').text("As senhas devem ter pelo menos 6 caracteres.");
          $('#error-message-password').show();
        } else if (password !== password_confirmation) {
          $('#error-message-password').text("As senhas não coincidem.");
          $('#error-message-password').show();
        } else {
          $.ajax({
            type: 'POST',
            url: '{{ route("password.update-code") }}',
            data: formData,
            success: function(response) {
              if (response.error) {
                $('#error-message-password').text(response.error);
                $('#error-message-password').show();
              } else {
                if ($('#error-message-password').is(":visible")) {
                  $('#error-message-password').hide();
                }

                $('#sucess-message-password').text(response.message);
                $('#sucess-message-password').show();

                setTimeout(function() {
          $('#passwordmodal').modal('hide');
        }, 2000);
              }
            },
            error: function(xhr, status, error) {
              $('#error-message-password').text(error);
              $('#error-message-password').show();
            }
          });
        }
      });
    });
  </script>
