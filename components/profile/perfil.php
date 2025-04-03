<div class="col-lg-12 col-md-12 col-sm-12 px-2 mt- bg-transparent">
                        <div class="container">
                            <!-- Toast Container -->
                            <div class="toast-container top-0 bottom-0 end-0 p-3">
                                <div id="myToast" class="toast <?php echo $tipo_mensaje === 'success' ? 'bg-lime-light' : 'bg-amber-light'; ?>" role="alert" aria-live="assertive" aria-atomic="true" style="display: <?php echo !empty($mensaje) ? 'block' : 'none'; ?>;" data-bs-autohide="true" data-bs-delay="5000">
                                    <div class="toast-header">
                                        <strong class="me-auto"><i class="bi bi-exclamation-square-fill"></i> <?php echo $tipo_mensaje === 'success' ? 'Éxito' : 'Error'; ?></strong>
                                        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                                    </div>
                                    <div class="toast-body">
                                        <?php echo $mensaje; ?>
                                    </div>
                                </div>

                            </div>
                            <div class="row">
                                <!--Actualizar datos usuario-->
                                <?php
                                $query = mysqli_query($conn, "SELECT * FROM users WHERE username like '%$usaurio%'");
                                while ($userLog = mysqli_fetch_array($query)) {
                                    $name = $userLog['nombre'];
                                    $phone = $userLog['telefono'];
                                    $email = $userLog['email'];
                                    $year = $userLog['edad'];
                                    $genero = $userLog['genero'];
                                    $department = $userLog['rol'];
                                    $direccion = $userLog['direccion'];
                                    $foto = $userLog['foto'];
                                }


                                ?>
                                <div class="col-lg-4 col-md-4 col-sm-12 px-2 mt-1">
                                    <h4>Actualizar foto</h4>
                                    <img src="<?php echo $foto; ?>" alt='Perfil' class="rounded p-1" width="100%" />
                                    <br>
                                    <form action="" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="formType" value="updatePictureProfile">
                                        <input type="hidden" name="usuario" value="<?php echo htmlspecialchars($usaurio); ?>"> <!-- Campo oculto para el identificador -->

                                        Selecciona una imagen para subir
                                        <input type="file" name="image" style="cursor: pointer" title="Seleccionar una imagen" />
                                        <br><br>
                                        <input type="submit" name="submit" class="btn bg-magenta-dark text-white" value="ACTUALIZAR FOTO" />
                                    </form>


                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12 px-2 mt-1">
                                    <img src="img/icons/pass.png" alt="pass" width="100%">
                                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                                        <input type="hidden" name="formType" value="updatePassword">
                                        <input type="hidden" name="usuario" value="<?php echo htmlspecialchars($usaurio); ?>"> <!-- Campo oculto para el identificador -->

                                        <h4>Actualizar contraseña</h4>

                                        <div class="form-group <?php echo (!empty($new_password_err)) ? 'has-error' : ''; ?>">
                                            <label for="password">Nueva contraseña</label>
                                            <input type="password" id="password" name="new_password" class="form-control" required>
                                            <span class="help-block"><?php echo isset($new_password_err) ? $new_password_err : ''; ?></span>
                                        </div>

                                        <div class="form-group <?php echo (!empty($confirm_password_err)) ? 'has-error' : ''; ?>">
                                            <label for="confirm_password">Confirmar contraseña</label>
                                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                                            <span class="help-block"><?php echo isset($confirm_password_err) ? $confirm_password_err : ''; ?></span>
                                        </div>

                                        <div class="form-group m-3">
                                            <input type="submit" class="btn bg-magenta-dark text-white" value="Actualizar contraseña">
                                            <a class="btn btn-outline-danger" href="main.php">Cancelar</a>
                                        </div>
                                    </form>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12 px-2 mt-1">
                                    <?php
                                    function getRoleName($department)
                                    {
                                        switch ($department) {
                                            case 1:
                                                return "Administrador";
                                            case 2:
                                                return "Operario";
                                            case 3:
                                                return "Aprobador";
                                            case 4:
                                                return "Editor";
                                            default:
                                                return "Rol desconocido";
                                        }
                                    }

                                    ?>
                                    <form action="" method="POST">
                                        <input type="hidden" name="usaurio" value="<?php echo htmlspecialchars($usaurio); ?>"> <!-- Cambia 'username' a 'usaurio' -->

                                        <input type="hidden" name="formType" value="updateUser">
                                        <h4>Actualizar información personal</h4>
                                        <div class="form-group">
                                            <label>Nombre</label>
                                            <input type="text" name="updName" class="form-control" value="<?php echo $name; ?>" require>
                                        </div>
                                        <div class="form-group">
                                            <label>Teléfono</label>
                                            <input type="text" name="updPhone" class="form-control" value="<?php echo $phone; ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Email</label>
                                            <input type="email" name="updEmail" class="form-control" value="<?php echo $email; ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Edad</label>
                                            <input type="number" name="updYear" class="form-control" value="<?php echo $year; ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Dirección</label>
                                            <input type="text" name="updAdress" class="form-control" value="<?php echo $direccion; ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Género</label>
                                            <select class="form-control" name="updGenero">
                                                <option value="<?php echo $genero; ?>"><?php echo $genero; ?>
                                                </option>
                                                <option value="Masculino">Masculino</option>
                                                <option value="Femenino">Femenino</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Dependencia</label>
                                            <select class="form-control" name="updDepartmen">
                                                <option value="<?php echo htmlspecialchars($department); ?>">
                                                    <?php echo htmlspecialchars(getRoleName($department)); ?>
                                                </option>
                                                <option value="1">Administrador</option>
                                                <option value="2">Operario</option>
                                                <option value="3">Aprobador</option>
                                                <option value="4">Editor</option>
                                            </select>
                                        </div>
                                        <div class="form-group m-3">
                                            <input type="submit" class="btn bg-magenta-dark text-white" value="Actualizar datos" name="actualizarUsuario">
                                            <a class="btn btn-outline-danger" href="main.php">Cancelar</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>