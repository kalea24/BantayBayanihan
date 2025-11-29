// Drill Mode JavaScript

let currentDrill = null;
let completedTasks = [];

// Drill data
const drillData = {
    earthquake: {
        title: '🌍 Earthquake Preparedness Drill',
        tasks: [
            { id: 'eq_1', name: 'Learn Drop, Cover, Hold technique', points: 10, description: 'Practice the proper earthquake response' },
            { id: 'eq_2', name: 'Identify safe spots in your home', points: 15, description: 'Under sturdy furniture, away from windows' },
            { id: 'eq_3', name: 'Prepare earthquake emergency kit', points: 20, description: 'Water, flashlight, first aid' },
            { id: 'eq_4', name: 'Create family emergency plan', points: 15, description: 'Meeting point and contact list' }
        ],
        quiz: [
            {
                question: 'What should you do first during an earthquake?',
                options: ['Run outside', 'Drop, Cover, Hold', 'Call emergency', 'Open windows'],
                correct: 1
            },
            {
                question: 'Where is the safest place during an earthquake?',
                options: ['Near windows', 'Under sturdy table', 'In elevator', 'On stairs'],
                correct: 1
            },
            {
                question: 'How long should you stay in position after shaking stops?',
                options: ['Stand up immediately', 'Wait 10 seconds', 'Wait for official clearance', 'Run outside'],
                correct: 2
            }
        ]
    },
    flood: {
        title: '🌊 Flood Preparedness Drill',
        tasks: [
            { id: 'fl_1', name: 'Know your evacuation route', points: 15, description: 'Identify nearest evacuation center' },
            { id: 'fl_2', name: 'Prepare go-bag', points: 20, description: 'Important documents, clothes, food' },
            { id: 'fl_3', name: 'Elevate valuables', points: 10, description: 'Move items to higher ground' },
            { id: 'fl_4', name: 'Check drainage around home', points: 15, description: 'Clear gutters and drains' }
        ],
        quiz: [
            {
                question: 'When should you evacuate during a flood?',
                options: ['When water reaches your knees', 'When authorities announce', 'When it stops raining', 'Never evacuate'],
                correct: 1
            },
            {
                question: 'How deep does water need to be to sweep away a car?',
                options: ['1 foot', '2 feet', '3 feet', '6 inches'],
                correct: 3
            }
        ]
    },
    fire: {
        title: '🔥 Fire Safety Drill',
        tasks: [
            { id: 'fi_1', name: 'Check smoke detectors', points: 10, description: 'Test all smoke alarms' },
            { id: 'fi_2', name: 'Plan fire escape routes', points: 15, description: 'Two ways out of each room' },
            { id: 'fi_3', name: 'Practice Stop, Drop, Roll', points: 10, description: 'Learn proper technique' },
            { id: 'fi_4', name: 'Identify fire extinguisher locations', points: 15, description: 'Know where they are' }
        ],
        quiz: [
            {
                question: 'If your clothes catch fire, what should you do?',
                options: ['Run', 'Stop, Drop, Roll', 'Pour water', 'Remove clothes'],
                correct: 1
            },
            {
                question: 'How often should you test smoke detectors?',
                options: ['Every year', 'Every 6 months', 'Every month', 'Never'],
                correct: 2
            }
        ]
    },
    typhoon: {
        title: '🌀 Typhoon Preparedness Drill',
        tasks: [
            { id: 'ty_1', name: 'Monitor weather updates', points: 10, description: 'Follow PAGASA announcements' },
            { id: 'ty_2', name: 'Secure loose outdoor items', points: 15, description: 'Bring inside or tie down' },
            { id: 'ty_3', name: 'Stock emergency supplies', points: 20, description: '3-day water and food supply' },
            { id: 'ty_4', name: 'Charge devices and powerbanks', points: 10, description: 'Prepare for power outage' }
        ],
        quiz: [
            {
                question: 'What is Signal No. 3?',
                options: ['Light winds', 'Moderate winds', 'Strong winds', 'Extreme winds'],
                correct: 2
            },
            {
                question: 'When should you stay indoors during a typhoon?',
                options: ['Only at night', 'During the entire typhoon', 'Only during eye', 'Never'],
                correct: 1
            }
        ]
    }
};

// Load drill
function loadDrill(drillType) {
    currentDrill = drillType;
    const drill = drillData[drillType];
    
    document.getElementById('drillTitle').textContent = drill.title;
    document.getElementById('drillContent').style.display = 'block';
    
    // Load tasks
    loadTasks(drill.tasks);
    
    // Load progress
    loadProgress();
    
    // Scroll to content
    document.getElementById('drillContent').scrollIntoView({ behavior: 'smooth' });
}

// Load tasks
function loadTasks(tasks) {
    const tasksList = document.getElementById('tasksList');
    tasksList.innerHTML = '<h5>Tasks</h5>';
    
    tasks.forEach(task => {
        const isCompleted = completedTasks.includes(task.id);
        const taskHtml = `
            <div class="task-item ${isCompleted ? 'task-completed' : ''}" id="task-${task.id}">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6>${task.name} ${isCompleted ? '✅' : ''}</h6>
                        <small class="text-muted">${task.description}</small>
                    </div>
                    <div>
                        ${!isCompleted ? 
                            `<button class="btn btn-sm btn-primary" onclick="completeTask('${task.id}', '${task.name}', ${task.points})">
                                Complete (+${task.points} pts)
                            </button>` :
                            `<span class="badge bg-success">Completed</span>`
                        }
                    </div>
                </div>
            </div>
        `;
        tasksList.innerHTML += taskHtml;
    });
    
    // Add quiz button
    tasksList.innerHTML += `
        <div class="mt-4">
            <button class="btn btn-warning" onclick="showQuiz()">
                📝 Take Knowledge Quiz
            </button>
        </div>
    `;
}

// Complete task
async function completeTask(taskId, taskName, points) {
    try {
        const response = await fetch('../api/drill/complete-task.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                drill_type: currentDrill,
                task_id: taskId,
                task_name: taskName,
                points_earned: points
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            completedTasks.push(taskId);
            
            // Update UI
            const taskElement = document.getElementById(`task-${taskId}`);
            taskElement.classList.add('task-completed');
            taskElement.querySelector('.btn').replaceWith('<span class="badge bg-success">Completed</span>');
            taskElement.querySelector('h6').innerHTML += ' ✅';
            
            // Update points
            const currentPoints = parseInt(document.getElementById('totalPoints').textContent);
            document.getElementById('totalPoints').textContent = currentPoints + points;
            
            // Update progress
            loadProgress();
            
            // Show toast
            showToast(`🎉 Task completed! +${points} points`, 'success');
        } else {
            showToast(data.message || 'Task completion failed', 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error completing task', 'danger');
    }
}

// Show quiz
function showQuiz() {
    const drill = drillData[currentDrill];
    const quizSection = document.getElementById('quizSection');
    const quizQuestions = document.getElementById('quizQuestions');
    
    quizQuestions.innerHTML = '';
    
    drill.quiz.forEach((q, index) => {
        const questionHtml = `
            <div class="mb-3">
                <p><strong>Q${index + 1}.</strong> ${q.question}</p>
                ${q.options.map((option, i) => `
                    <div class="form-check">
                        <input class="form-check-input" type="radio" 
                               name="q${index}" value="${i}" id="q${index}_${i}">
                        <label class="form-check-label" for="q${index}_${i}">
                            ${option}
                        </label>
                    </div>
                `).join('')}
            </div>
        `;
        quizQuestions.innerHTML += questionHtml;
    });
    
    quizSection.style.display = 'block';
    quizSection.scrollIntoView({ behavior: 'smooth' });
}

// Submit quiz
async function submitQuiz() {
    const drill = drillData[currentDrill];
    let score = 0;
    
    drill.quiz.forEach((q, index) => {
        const selected = document.querySelector(`input[name="q${index}"]:checked`);
        if (selected && parseInt(selected.value) === q.correct) {
            score++;
        }
    });
    
    try {
        const response = await fetch('../api/drill/submit-quiz.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                drill_type: currentDrill,
                quiz_id: `${currentDrill}_quiz`,
                score: score,
                total_questions: drill.quiz.length
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast(`🎉 Quiz completed! Score: ${score}/${drill.quiz.length} (+${data.points_earned} points)`, 'success');
            
            // Update points
            const currentPoints = parseInt(document.getElementById('totalPoints').textContent);
            document.getElementById('totalPoints').textContent = currentPoints + data.points_earned;
            
            // Hide quiz
            document.getElementById('quizSection').style.display = 'none';
            
            // Reload to show new badges
            setTimeout(() => location.reload(), 2000);
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error submitting quiz', 'danger');
    }
}

// Load progress
async function loadProgress() {
    try {
        const response = await fetch(`../api/drill/get-progress.php?drill_type=${currentDrill}`);
        const data = await response.json();
        
        if (data.success && data.data) {
            const progress = data.data;
            completedTasks = JSON.parse(progress.completed_tasks).map(t => t.taskId);
            
            // Calculate progress percentage
            const totalTasks = drillData[currentDrill].tasks.length + 1; // tasks + quiz
            const completed = completedTasks.length + (JSON.parse(progress.quiz_results).length > 0 ? 1 : 0);
            const percentage = Math.round((completed / totalTasks) * 100);
            
            // Update progress bar
            const progressBar = document.getElementById('progressBar');
            progressBar.style.width = percentage + '%';
            progressBar.textContent = percentage + '%';
            
            // Update progress details
            document.getElementById('progressDetails').innerHTML = `
                <p><strong>Tasks:</strong> ${completedTasks.length}/${drillData[currentDrill].tasks.length}</p>
                <p><strong>Points:</strong> ${progress.total_points}</p>
                <p><strong>Status:</strong> <span class="badge bg-info">${progress.status}</span></p>
            `;
        }
    } catch (error) {
        console.error('Error loading progress:', error);
    }
}

// Show toast notification
function showToast(message, type) {
    const toastHtml = `
        <div class="toast align-items-center text-white bg-${type} border-0" role="alert" 
             style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" 
                        data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', toastHtml);
    const toastElement = document.querySelector('.toast:last-child');
    const toast = new bootstrap.Toast(toastElement);
    toast.show();
    
    setTimeout(() => toastElement.remove(), 5000);
}

// Load badges when modal opens
document.getElementById('badgesModal').addEventListener('show.bs.modal', async function() {
    try {
        const response = await fetch('../api/drill/get-badges.php');
        const data = await response.json();
        
        if (data.success) {
            const badgesContent = document.getElementById('badgesContent');
            
            if (data.data.length === 0) {
                badgesContent.innerHTML = '<p class="text-center">No badges earned yet. Complete tasks to earn badges!</p>';
            } else {
                badgesContent.innerHTML = '<div class="row">';
                data.data.forEach(badge => {
                    badgesContent.innerHTML += `
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h1>🏆</h1>
                                    <h5>${badge.name}</h5>
                                    <p class="text-muted">${badge.description}</p>
                                    <small>Earned: ${new Date(badge.earned_at).toLocaleDateString()}</small>
                                </div>
                            </div>
                        </div>
                    `;
                });
                badgesContent.innerHTML += '</div>';
            }
        }
    } catch (error) {
        console.error('Error loading badges:', error);
    }
});