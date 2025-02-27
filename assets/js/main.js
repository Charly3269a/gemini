    function deleteTask(id) {
            if (confirm('¿Está seguro de que desea eliminar esta tarea?')) {
                fetch(`/bolt/pages/tasks/delete.php?id=${id}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert('Error al eliminar la tarea');
                    }
                });
            }
        }

        function archiveTask(id) {
            if (confirm('¿Está seguro de que desea archivar esta tarea?')) {
                fetch(`/bolt/pages/tasks/archive.php?id=${id}`, {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert('Error al archivar la tarea');
                    }
                });
            }
        }

        // Actualizar estado de subtareas
    document.addEventListener('DOMContentLoaded', function() {
        const checkboxes = document.querySelectorAll('.subtask-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const taskId = this.dataset.taskId;
                const subtaskId = this.dataset.subtaskId;
                const completed = this.checked;

                // Actualizar el estado de la subtarea en la base de datos
                fetch('/bolt/pages/tasks/update_subtask.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `task_id=${taskId}&subtask_id=${subtaskId}&completed=${completed ? 1 : 0}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Actualizar el progreso visual
                        const row = document.querySelector(`tr[data-task-id="${taskId}"]`);
                        const progressElement = row.querySelector('.task-progress');
                        progressElement.textContent = `${data.completed_subtasks}/${data.total_subtasks}`;

                        // Actualizar el estado de la tarea si es necesario
                        if (data.should_update_status) {
                            const statusSelect = row.querySelector('.status-select');
                            statusSelect.value = data.completed_subtasks === data.total_subtasks ? 'completed' : 'pending';
                            updateTaskStatus(taskId, statusSelect.value);
                        }
                    }
                });
            });
        });
    });
function updateTaskStatus(taskId, status) {
    const row = document.querySelector(`tr[data-task-id="${taskId}"]`);
    if (!row) return;

    // Remove existing status classes
    row.classList.remove('task-pending', 'task-problems', 'task-completed'); // Corrected class name
    
    // Add new status class
    row.classList.add(`task-${status.toLowerCase()}`);

    // Show/hide archive button
    const archiveButton = row.querySelector('.archive-button');
    if (archiveButton) {
        archiveButton.style.display = status === 'completed' ? 'inline-flex' : 'none'; //Corrected status
    }

    // Update status in database
    fetch('/bolt/pages/tasks/update_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `task_id=${taskId}&status=${status}`
    });
}
// Google Maps modal handler
function openMapModal(mapUrl) {
    const overlay = document.createElement('div');
    overlay.id = 'map-overlay';
    overlay.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50'; // Added z-50

    const modalContent = document.createElement('div');
    modalContent.className = 'bg-white p-4 rounded-lg shadow-lg w-3/4 h-3/4 relative'; // Added relative

    const closeButton = document.createElement('button');
    closeButton.className = 'absolute top-2 right-2 text-gray-600 hover:text-gray-900';
    closeButton.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>`;
    closeButton.onclick = closeMapModal;

    const iframe = document.createElement('iframe');
    iframe.src = mapUrl;
    iframe.width = '100%';
    iframe.height = '100%';
    iframe.style.border = '0';
    iframe.allowFullscreen = true;

    modalContent.appendChild(closeButton);
    modalContent.appendChild(iframe);
    overlay.appendChild(modalContent);
    document.body.appendChild(overlay);
}

function closeMapModal() {
    const overlay = document.getElementById('map-overlay');
    if (overlay) {
        document.body.removeChild(overlay);
    }
}

        function embedMap(mapUrl, container) {
            const iframe = document.createElement('iframe');
            iframe.src = mapUrl;  // Use the URL directly
            iframe.width = '100%';
            iframe.height = '300';
            iframe.style.border = '0';
            iframe.allowFullscreen = true;

            container.innerHTML = '';
            container.appendChild(iframe);
        }
   
function deleteBeforePhoto(taskId) {
    if (confirm('¿Está seguro de que desea eliminar la foto de antes?')) {
        deleteTaskPhoto(taskId, 'before_photo');
    }
}

function deleteAfterPhoto(taskId) {
    if (confirm('¿Está seguro de que desea eliminar la foto de después?')) {
        deleteTaskPhoto(taskId, 'after_photo');
    }
}




    function handlePhotoUpload(input, taskId, photoType) {
    const file = input.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('file', file);
    formData.append('task_id', taskId);
    formData.append('photo_type', photoType);

    fetch('/bolt/pages/tasks/upload_task_photo.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const cell = input.closest('.photo-upload-container');
            
            // Crear el contenedor de la foto
            const photoActions = document.createElement('div');
            photoActions.className = 'photo-actions';

            // Crear la miniatura
            const thumbnail = document.createElement('img');
            thumbnail.src = data.photo_url;
            thumbnail.alt = photoType === 'before_photo' ? 'Foto Antes' : 'Foto Después';
            thumbnail.className = 'photo-thumbnail';
            thumbnail.onclick = () => window.open(data.photo_url, '_blank');

            // Crear el botón de eliminar
            const deleteButton = document.createElement('button');
            deleteButton.className = 'text-red-600 hover:text-red-900 text-xs';
            deleteButton.textContent = 'Eliminar';
            deleteButton.onclick = () => deleteTaskPhoto(taskId, photoType);

            // Ensamblar la estructura
            photoActions.appendChild(thumbnail);
            photoActions.appendChild(deleteButton);

            // Limpiar el contenedor y agregar los nuevos elementos
            cell.innerHTML = '';
            cell.appendChild(photoActions);
        } else {
            alert('Error al subir la foto: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error de red al subir la foto.');
    });
}

function deleteTaskPhoto(taskId, photoType) {
    if (!confirm('¿Está seguro de que desea eliminar esta foto?')) return;

    fetch('/bolt/pages/tasks/delete_task_photo.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `task_id=${taskId}&photo_type=${photoType}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Encontrar el contenedor correcto usando el tr padre
            const row = document.querySelector(`tr[data-task-id="${taskId}"]`);
            const cell = row.querySelector(`.photo-upload-container`);
            
            // Recrear el botón de subida
            cell.innerHTML = `
                <input type="file" 
                       id="${photoType}_${taskId}" 
                       class="hidden" 
                       accept="image/*"
                       onchange="handlePhotoUpload(this, ${taskId}, '${photoType}')" />
                <button onclick="document.getElementById('${photoType}_${taskId}').click()" 
                        class="btn-upload-photo">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4 5a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V7a2 2 0 00-2-2h-1.586a1 1 0 01-.707-.293l-1.121-1.121A2 2 0 0011.172 3H8.828a2 2 0 00-1.414.586L6.293 4.707A1 1 0 015.586 5H4zm6 9a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                    </svg>
                </button>
            `;
        } else {
            alert('Error al eliminar la foto: ' + (data.error || 'Error desconocido'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Mejorar el mensaje de error para ser más específico
        alert('Hubo un error en la red, pero la foto se eliminó correctamente. Por favor, actualice la página si no ve el botón de subida.');
    });
}

// Inicialización de eventos
document.addEventListener('DOMContentLoaded', () => {
    // Eventos para subtareas
    const checkboxes = document.querySelectorAll('.subtask-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const taskId = this.dataset.taskId;
            const subtaskId = this.dataset.subtaskId;
            const completed = this.checked;

            fetch('/bolt/pages/tasks/update_subtask.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `task_id=${taskId}&subtask_id=${subtaskId}&completed=${completed ? 1 : 0}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const row = document.querySelector(`tr[data-task-id="${taskId}"]`);
                    const progressElement = row.querySelector('.task-progress');
                    progressElement.textContent = `${data.completed_subtasks}/${data.total_subtasks}`;

                    if (data.should_update_status) {
                        const statusSelect = row.querySelector('.status-select');
                        statusSelect.value = data.completed_subtasks === data.total_subtasks ? 'completed' : 'pending';
                        updateTaskStatus(taskId, statusSelect.value);
                    }
                }
            });
        });
    });
});
   function openModal(imageSrc) {
        const modal = document.getElementById('imageModal');
        const modalImg = document.getElementById('modalImage');
        modalImg.src = imageSrc;
        modal.style.display = "block";
    }

    function closeModal() {
        const modal = document.getElementById('imageModal');
        modal.style.display = "none";
    }
    
    function saveTaskNotes(taskId, notesElement) {
    const notes = notesElement.value;
    fetch('/bolt/pages/tasks/save_task_notes.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `task_id=${taskId}&notes=${encodeURIComponent(notes)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Optionally provide visual feedback to the user (e.g., tooltip or message)
            console.log('Notes saved successfully for task ID:', taskId);
        } else {
            alert('Error al guardar las notas: '  data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error de red al guardar las notas.');
    });
}

function clearTaskNotes(taskId, notesElement) {
    if (confirm('¿Está seguro de que desea borrar las notas de esta tarea?')) {
        fetch('/bolt/pages/tasks/clear_task_notes.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `task_id=${taskId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                notesElement.value = ''; // Clear textarea
                console.log('Notes cleared successfully for task ID:', taskId);
            } else {
                alert('Error al borrar las notas: '  data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error de red al borrar las notas.');
        });
    }
}
function saveTaskNotes(taskId, notesElement) {
    const notes = notesElement.value;
    fetch('/bolt/pages/tasks/save_task_notes.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `task_id=${taskId}&notes=${encodeURIComponent(notes)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Optionally provide visual feedback to the user (e.g., tooltip or message)
            console.log('Notes saved successfully for task ID:', taskId);
        } else {
            alert('Error al guardar las notas: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error de red al guardar las notas.');
    });
}

function clearTaskNotes(taskId, notesElement) {
    if (confirm('¿Está seguro de que desea borrar las notas de esta tarea?')) {
        fetch('/bolt/pages/tasks/clear_task_notes.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `task_id=${taskId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                notesElement.value = ''; // Clear textarea
                console.log('Notes cleared successfully for task ID:', taskId);
            } else {
                alert('Error al borrar las notas: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error de red al borrar las notas.');
        });
    }
}