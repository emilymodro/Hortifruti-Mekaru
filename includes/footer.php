<?php
// Arquivo: includes/footer.php
?>
                </div>
                </div>
            </div>
        </div>
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <script src="admin/vendor/jquery/jquery.min.js"></script>
    <script src="admin/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="admin/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="admin/js/sb-admin-2.min.js"></script>
    <script>
    $(document).ready(function() {
        // Função para obter o HTML do novo contato (sem valores)
        function getNovoContatoHtml() {
            return `
                <div class="row mb-2 contato-item">
                    <div class="col-md-4">
                        <select name="contato_tipo[]" class="form-control" required>
                            <option value="">Selecione o Tipo</option>
                            <option value="Celular">Celular</option>
                            <option value="Telefone">Telefone</option>
                            <option value="Email">Email</option>
                            <option value="Outro">Outro</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="contato_valor[]" class="form-control" placeholder="Valor do Contato" required>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-danger btn-sm remover-contato">Remover</button>
                    </div>
                </div>
            `;
        }

        // Função para obter o HTML da nova chave PIX (sem valores)
        function getNovaPixHtml() {
            return `
                <div class="row mb-2 pix-item">
                    <div class="col-md-4">
                        <select name="pix_tipo[]" class="form-control">
                            <option value="">Selecione o Tipo</option>
                            <option value="CPF">CPF</option>
                            <option value="CNPJ">CNPJ</option>
                            <option value="Email">Email</option>
                            <option value="Telefone">Telefone</option>
                            <option value="Aleatória">Chave Aleatória</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="pix_chave[]" class="form-control" placeholder="Valor da Chave PIX">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-danger btn-sm remover-pix">Remover</button>
                    </div>
                </div>
            `;
        }

        // --- Lógica para Adicionar/Remover Contato ---
        $('#adicionar_contato').click(function() {
            $('#contatos_area').append(getNovoContatoHtml());
            updateRemoveButtons('#contatos_area', '.remover-contato');
        });

        $(document).on('click', '.remover-contato', function() {
            $(this).closest('.contato-item').remove();
            updateRemoveButtons('#contatos_area', '.remover-contato');
        });

        // --- Lógica para Adicionar/Remover PIX ---
        $('#adicionar_pix').click(function() {
            $('#pix_area').append(getNovaPixHtml());
            updateRemoveButtons('#pix_area', '.remover-pix');
        });

        $(document).on('click', '.remover-pix', function() {
            $(this).closest('.pix-item').remove();
            updateRemoveButtons('#pix_area', '.remover-pix');
        });
        
        // Função para mostrar/esconder o botão Remover apenas se houver mais de 1 item
        function updateRemoveButtons(area_id, btn_class) {
            // Encontra o número de elementos filhos de linha (contato-item ou pix-item)
            var itemCount = $(area_id).find('.row.mb-2').length;
            
            if (itemCount > 1) { 
                $(area_id).find(btn_class).show();
            } else {
                // Se houver apenas 1 item, oculta o botão remover dele
                $(area_id).find(btn_class).hide();
            }
        }
        
        // Inicialização: Garante que os botões "Remover" iniciais estejam corretos
        // NOTA: Isso deve ser aplicado a AMBAS as páginas (cadastro e edição)
        updateRemoveButtons('#contatos_area', '.remover-contato');
        updateRemoveButtons('#pix_area', '.remover-pix');
    });
    </script>
    </body>
</html>

        <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Pronto para Sair?</h5>
                        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">Selecione "Sair" abaixo se você estiver pronto para encerrar sua sessão atual.</div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                        <a class="btn btn-primary" href="logout.php">Sair</a>
                    </div>
                </div>
            </div>
        </div>

    </body>
</html>