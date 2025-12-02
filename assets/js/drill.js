// Drill Mode JavaScript - Enhanced Version
console.log('Drill.js loaded successfully!');
let currentDrill = null;
let currentPhase = 'learning';
let currentPracticeIndex = -1;
let totalPointsEarned = 0;
let assessmentTimer = null;
let assessmentStartTime = null;
let timeRemainingSeconds = 600; // 10 minutes = 600 seconds
let practiceUsedIndices = [];

// Main function - called when drill card is clicked
function selectDrill(drillType) {
    console.log('Drill selected:', drillType);
    currentDrill = drillType;
    totalPointsEarned = 0;
    practiceUsedIndices = [];
    loadDrill(drillType);
}

// Drill data (EXPANDED practice scenarios to 10 per drill)
const drillData = {
    earthquake: {
        title: '🌍 Earthquake Preparedness Drill',
        learning: {
            content: `
                <div class="alert alert-info">
                    <h6>What You'll Learn:</h6>
                    <ul>
                        <li>Understanding earthquake risks</li>
                        <li>The "Drop, Cover, Hold" technique</li>
                        <li>Safe spots during earthquakes</li>
                        <li>Emergency kit preparation</li>
                    </ul>
                </div>
                <h6>1. Understanding Earthquakes</h6>
                <p>Earthquakes occur when tectonic plates shift suddenly. The Philippines sits on the Pacific Ring of Fire.</p>
                <h6>2. Drop, Cover, Hold Technique</h6>
                <p><strong>DROP</strong> to your hands and knees immediately.</p>
                <p><strong>COVER</strong> your head and neck under a sturdy table.</p>
                <p><strong>HOLD ON</strong> until shaking stops.</p>
                <h6>3. Safe Spots</h6>
                <ul>
                    <li>✅ Under sturdy furniture</li>
                    <li>✅ Against interior wall</li>
                    <li>❌ Near windows</li>
                    <li>❌ Under ceiling fans</li>
                </ul>
            `
        },
        scenarios: [
            {
                id: 'eq_sc1',
                situation: 'You are in your living room when shaking starts. Table is 3 meters away, doorway is 1 meter away.',
                question: 'What should you do FIRST?',
                options: [
                    'Run to the table',
                    'Drop where you are and cover your head with your arms',
                    'Stand in the doorway',
                    'Run outside immediately'
                ],
                correct: 1,
                explanation: 'Drop immediately where you are to prevent falling. Running during shaking is dangerous.',
                points: 15
            },
            {
                id: 'eq_sc2',
                situation: 'Earthquake stops. You smell gas and see cracks in the wall.',
                question: 'What is the BEST action?',
                options: [
                    'Wait for aftershocks to stop first',
                    'Evacuate the building immediately',
                    'Take photos of the damage',
                    'Turn on lights to see better'
                ],
                correct: 1,
                explanation: 'Gas leak combined with structural damage means immediate danger. Evacuate quickly.',
                points: 15
            },
            {
                id: 'eq_sc3',
                situation: 'You are on the 5th floor when earthquake happens. Elevator is right next to you.',
                question: 'Should you use the elevator to evacuate?',
                options: [
                    'Yes, it\'s the fastest way down',
                    'No, use stairs after shaking completely stops',
                    'Yes, but only if it\'s empty',
                    'Yes, if it\'s a modern building'
                ],
                correct: 1,
                explanation: 'NEVER use elevators during or after earthquakes. Power can fail anytime.',
                points: 15
            },
            {
                id: 'eq_sc4',
                situation: 'You are cooking when an earthquake strikes. Pots are falling.',
                question: 'What should you do?',
                options: [
                    'Turn off the stove first',
                    'Drop, cover, and hold on immediately',
                    'Grab the fire extinguisher',
                    'Shut the kitchen door'
                ],
                correct: 1,
                explanation: 'Your safety comes first. Protect yourself before addressing other hazards.',
                points: 15
            },
            {
                id: 'eq_sc5',
                situation: 'You are in a wheelchair during an earthquake.',
                question: 'What is the safest action?',
                options: [
                    'Try to stand up and drop',
                    'Stay in your wheelchair, lock wheels, and cover your head',
                    'Roll quickly out of the room',
                    'Call for help immediately'
                ],
                correct: 1,
                explanation: 'Stay in your wheelchair, lock the wheels, and protect your head and neck.',
                points: 15
            },
            {
                id: 'eq_sc6',
                situation: 'After shaking stops, you hear a loud hissing sound.',
                question: 'What does this likely mean?',
                options: [
                    'Water pipe burst',
                    'Gas leak',
                    'Wind outside',
                    'Electrical short'
                ],
                correct: 1,
                explanation: 'Hissing often indicates a gas leak. Evacuate and avoid sparks or flames.',
                points: 15
            },
            {
                id: 'eq_sc7',
                situation: 'You are in a store during an earthquake. Shelves are falling.',
                question: 'Where should you go?',
                options: [
                    'Run to the exit',
                    'Get under a shopping cart',
                    'Move away from shelves and drop next to a sturdy structure',
                    'Stand in the middle of the aisle'
                ],
                correct: 2,
                explanation: 'Avoid falling objects. Get next to sturdy items that won\'t collapse on you.',
                points: 15
            },
            {
                id: 'eq_sc8',
                situation: 'You are with a child during an earthquake.',
                question: 'What should you do?',
                options: [
                    'Tell the child to run outside',
                    'Drop, cover, and hold the child under you',
                    'Put the child in a closet',
                    'Hold the child\'s hand and run'
                ],
                correct: 1,
                explanation: 'Protect the child by covering them with your body during shaking.',
                points: 15
            },
            {
                id: 'eq_sc9',
                situation: 'You are driving when an earthquake occurs.',
                question: 'What should you do?',
                options: [
                    'Stop under a bridge for shelter',
                    'Pull over, stop, and stay in the vehicle',
                    'Get out and lie on the road',
                    'Speed up to get home quickly'
                ],
                correct: 1,
                explanation: 'Stop safely away from buildings, trees, and overpasses. Stay inside.',
                points: 15
            },
            {
                id: 'eq_sc10',
                situation: 'You are in bed when an earthquake strikes.',
                question: 'What should you do?',
                options: [
                    'Jump out of bed',
                    'Stay in bed, cover head with pillow',
                    'Run to the bathroom',
                    'Stand in the doorway'
                ],
                correct: 1,
                explanation: 'Stay in bed. Use your pillow to protect your head from falling debris.',
                points: 15
            }
        ],
        assessment: [
            {
                question: 'During an earthquake, you should immediately:',
                options: [
                    'Run outside to open space',
                    'Stand in a doorway and hold on',
                    'Drop, Cover, and Hold On',
                    'Hide under the stairs'
                ],
                correct: 2,
                points: 10
            },
            {
                question: 'How long should you stay in protective position after shaking stops?',
                options: [
                    'Stand up immediately to evacuate',
                    'Wait 10-15 seconds',
                    'Wait at least 60 seconds and be ready for aftershocks',
                    'Wait for emergency services to arrive'
                ],
                correct: 2,
                points: 10
            },
            {
                question: 'What is the MOST dangerous location during an earthquake?',
                options: [
                    'Under a sturdy table',
                    'Near windows or glass',
                    'Against an interior wall',
                    'In an open doorway'
                ],
                correct: 1,
                points: 10
            },
            {
                question: 'After an earthquake, you should:',
                options: [
                    'Immediately use your phone to call everyone',
                    'Check for injuries and damage, be ready for aftershocks',
                    'Go back inside to get your belongings',
                    'Turn on all lights to see the damage'
                ],
                correct: 1,
                points: 10
            },
            {
                question: 'If you are outdoors during an earthquake, you should:',
                options: [
                    'Run into the nearest building',
                    'Lie flat on the ground',
                    'Move to an open area away from buildings, trees, and power lines',
                    'Hold onto a tree for support'
                ],
                correct: 2,
                points: 10
            },
            {
                question: 'What should be in your earthquake emergency kit?',
                options: [
                    'Only food and water',
                    'Water, food, flashlight, first aid kit, and battery-powered radio',
                    'Just a flashlight and batteries',
                    'Only important documents'
                ],
                correct: 1,
                points: 10
            },
            {
                question: 'If you are in bed during an earthquake, you should:',
                options: [
                    'Jump out of bed and run',
                    'Stay in bed, cover your head with a pillow',
                    'Roll under the bed',
                    'Stand next to the bed'
                ],
                correct: 1,
                points: 10
            },
            {
                question: 'What are aftershocks?',
                options: [
                    'Smaller earthquakes that can occur after the main earthquake',
                    'The main earthquake happening again',
                    'Fake tremors you imagine after an earthquake',
                    'Only happen in big earthquakes'
                ],
                correct: 0,
                points: 10
            }
        ]
    },
    flood: {
        title: '🌊 Flood Preparedness Drill',
        learning: {
            content: `
                <h6>Understanding Floods</h6>
                <p>Floods are the most common disaster in the Philippines. They can develop slowly or as flash floods.</p>
                <h6>Warning Levels</h6>
                <ul>
                    <li><strong>Level 1 (Green):</strong> Normal conditions</li>
                    <li><strong>Level 2 (Yellow):</strong> Be alert, prepare to evacuate</li>
                    <li><strong>Level 3 (Orange):</strong> Evacuate now</li>
                    <li><strong>Level 4 (Red):</strong> Life-threatening</li>
                </ul>
                <h6>Safety Rules</h6>
                <ul>
                    <li>❌ Never drive through flood water - 6 inches can stall a car</li>
                    <li>❌ Never walk through moving water - 6 inches can knock you down</li>
                    <li>✅ Move to higher ground immediately</li>
                </ul>
            `
        },
        scenarios: [
            {
                id: 'fl_sc1',
                situation: 'Heavy rain for 2 hours. Water is ankle-deep on your street. Level 2 warning announced.',
                question: 'What should you do?',
                options: [
                    'Wait for Level 3 before doing anything',
                    'Prepare your go-bag and be ready to evacuate immediately',
                    'Try to drive to higher ground now',
                    'Nothing, it\'s only Level 2'
                ],
                correct: 1,
                explanation: 'Level 2 means PREPARE NOW. Water can rise very quickly.',
                points: 15
            },
            {
                id: 'fl_sc2',
                situation: 'You need to evacuate but there is knee-deep water between you and the evacuation center.',
                question: 'What is the safest action?',
                options: [
                    'Wade through quickly before it gets deeper',
                    'Drive your car through it',
                    'Wait for official rescue or find an alternate route on higher ground',
                    'Hold onto a rope and cross carefully'
                ],
                correct: 2,
                explanation: 'Knee-deep moving water is extremely dangerous. It can sweep you away.',
                points: 15
            },
            {
                id: 'fl_sc3',
                situation: 'Flood water is rising in your home. You are on the second floor.',
                question: 'What should you prioritize?',
                options: [
                    'Saving your valuables and electronics',
                    'Going to the highest point (roof if needed) and calling for help',
                    'Trying to stop the water from coming in',
                    'Going downstairs to check the damage'
                ],
                correct: 1,
                explanation: 'Your life is the priority. Get to the highest point possible.',
                points: 15
            },
            {
                id: 'fl_sc4',
                situation: 'Your car stalls in 1 foot of flood water.',
                question: 'What should you do?',
                options: [
                    'Stay in the car and wait for help',
                    'Get out immediately and move to higher ground',
                    'Try to restart the engine',
                    'Call insurance first'
                ],
                correct: 1,
                explanation: 'Just 12 inches of water can carry away vehicles. Get out and go uphill.',
                points: 15
            },
            {
                id: 'fl_sc5',
                situation: 'Flood warning issued for your area tomorrow.',
                question: 'What should you do NOW?',
                options: [
                    'Wait until flooding starts',
                    'Move valuables upstairs, prepare go-bag, charge devices',
                    'Go shopping for food',
                    'Ignore it unless it rains'
                ],
                correct: 1,
                explanation: 'Prepare before the flood arrives. Don’t wait until it’s too late.',
                points: 15
            },
            {
                id: 'fl_sc6',
                situation: 'You see downed power lines in flood water.',
                question: 'What should you do?',
                options: [
                    'Walk around them carefully',
                    'Assume they are live and stay far away',
                    'Use a stick to move them',
                    'Call the utility company and wait nearby'
                ],
                correct: 1,
                explanation: 'Flood water with downed lines can be electrified. Stay at least 30 feet away.',
                points: 15
            },
            {
                id: 'fl_sc7',
                situation: 'After a flood, your home has standing water inside.',
                question: 'What should you do before entering?',
                options: [
                    'Wade right in to assess damage',
                    'Turn on the main power to check if electricity works',
                    'Check for structural damage and turn off utilities first',
                    'Start cleaning immediately'
                ],
                correct: 2,
                explanation: 'Flood water can hide structural damage and electrical hazards.',
                points: 15
            },
            {
                id: 'fl_sc8',
                situation: 'You are trapped on your roof during a flash flood.',
                question: 'What is the best way to signal for help?',
                options: [
                    'Shout loudly',
                    'Use a whistle, mirror, or bright cloth to signal rescuers',
                    'Swim to the nearest house',
                    'Wait silently'
                ],
                correct: 1,
                explanation: 'Use visible or audible signals that don’t exhaust you or put you at risk.',
                points: 15
            },
            {
                id: 'fl_sc9',
                situation: 'Your neighbor is trying to drive through flood water.',
                question: 'What should you do?',
                options: [
                    'Wave them through',
                    'Yell a warning to stop them',
                    'Take a video',
                    'Go back inside'
                ],
                correct: 1,
                explanation: 'Speak up — you might save their life. “Turn around, don’t drown!”',
                points: 15
            },
            {
                id: 'fl_sc10',
                situation: 'You have a pet during a flood evacuation.',
                question: 'What should you do?',
                options: [
                    'Leave the pet behind — rescuers will find it',
                    'Bring your pet with your go-bag',
                    'Tie it outside safely',
                    'Let it roam freely'
                ],
                correct: 1,
                explanation: 'If it’s not safe for you, it’s not safe for your pet. Include them in your plan.',
                points: 15
            }
        ],
        assessment: [
            {
                question: 'How much water can knock down an adult?',
                options: ['2 inches', '6 inches', '1 foot', '2 feet'],
                correct: 1,
                points: 10
            },
            {
                question: 'When should you evacuate during a flood?',
                options: [
                    'Only at Level 4',
                    'When water enters your house',
                    'When authorities order evacuation (Level 3)',
                    'Never evacuate, stay home'
                ],
                correct: 2,
                points: 10
            },
            {
                question: 'What should be in your flood go-bag?',
                options: [
                    'Only clothes',
                    'Important documents, food, water, medications, flashlight',
                    'Just your phone',
                    'Heavy furniture'
                ],
                correct: 1,
                points: 10
            },
            {
                question: 'How much water can sweep away most cars?',
                options: ['6 inches', '1 foot', '2 feet', '3 feet'],
                correct: 2,
                points: 10
            },
            {
                question: 'If trapped in a car during flash flood, you should:',
                options: [
                    'Stay in the car and wait',
                    'Try to drive through the water',
                    'Get out immediately and move to higher ground',
                    'Call and wait for rescue'
                ],
                correct: 2,
                points: 10
            },
            {
                question: 'After a flood, before entering your home you should:',
                options: [
                    'Rush in to check damage',
                    'Check for structural damage and turn off utilities',
                    'Start cleaning immediately',
                    'Take photos for insurance'
                ],
                correct: 1,
                points: 10
            },
            {
                question: 'Flood water can be contaminated with:',
                options: [
                    'Only dirt',
                    'Sewage, chemicals, and debris',
                    'Just rainwater',
                    'Nothing dangerous'
                ],
                correct: 1,
                points: 10
            },
            {
                question: 'What is a flash flood?',
                options: [
                    'Flood that happens at night',
                    'Flood that occurs very quickly with little warning',
                    'Small flood',
                    'Flood from the ocean'
                ],
                correct: 1,
                points: 10
            }
        ]
    },
    fire: {
        title: '🔥 Fire Safety Drill',
        learning: {
            content: `
                <h6>Fire Prevention</h6>
                <p>Most fires are preventable. Common causes include unattended cooking, electrical faults, and candles.</p>
                <h6>Stop, Drop, Roll</h6>
                <ol>
                    <li><strong>STOP</strong> - Don't run (oxygen feeds fire)</li>
                    <li><strong>DROP</strong> - To the ground immediately</li>
                    <li><strong>ROLL</strong> - Back and forth covering face</li>
                </ol>
                <h6>Smoke Safety</h6>
                <p>Smoke rises. Stay low, crawl under smoke, cover mouth with cloth.</p>
            `
        },
        scenarios: [
            {
                id: 'fi_sc1',
                situation: 'Your clothes catch fire while cooking.',
                question: 'What should you do?',
                options: [
                    'Run to find water',
                    'Stop, Drop, and Roll immediately',
                    'Use fire extinguisher on yourself',
                    'Jump in the shower'
                ],
                correct: 1,
                explanation: 'Running makes flames worse. Stop, drop, and roll smothers the fire.',
                points: 15
            },
            {
                id: 'fi_sc2',
                situation: 'Smoke alarm goes off at night. Your room is filling with smoke.',
                question: 'How should you escape?',
                options: [
                    'Stand up and run to the door',
                    'Open all windows first',
                    'Crawl low under the smoke to the nearest exit',
                    'Hide under your bed'
                ],
                correct: 2,
                explanation: 'Crawl low where the air is cleaner. Smoke rises and is toxic.',
                points: 15
            },
            {
                id: 'fi_sc3',
                situation: 'You see smoke coming from under a closed door.',
                question: 'What should you do before opening it?',
                options: [
                    'Open it immediately to see what\'s happening',
                    'Feel the door and doorknob with the back of your hand first',
                    'Break the door down',
                    'Pour water under the door'
                ],
                correct: 1,
                explanation: 'If hot, fire is on the other side. Use another escape route.',
                points: 15
            },
            {
                id: 'fi_sc4',
                situation: 'A small pan fire starts while cooking.',
                question: 'What is the safest way to put it out?',
                options: [
                    'Pour water on it',
                    'Cover it with a metal lid and turn off the heat',
                    'Blow on it',
                    'Use a towel to smother it'
                ],
                correct: 1,
                explanation: 'Water spreads oil fires. Smothering cuts off oxygen safely.',
                points: 15
            },
            {
                id: 'fi_sc5',
                situation: 'You discover a fire in your home.',
                question: 'What is your FIRST priority?',
                options: [
                    'Grab your phone and wallet',
                    'Get everyone out immediately',
                    'Try to put out the fire yourself',
                    'Close all doors behind you'
                ],
                correct: 1,
                explanation: 'Life safety first. Get out, stay out, call for help.',
                points: 15
            },
            {
                id: 'fi_sc6',
                situation: 'Your escape route is blocked by fire.',
                question: 'What should you do?',
                options: [
                    'Try to run through it',
                    'Go to a room, close the door, and signal for help at the window',
                    'Jump from the second floor',
                    'Hide in the closet'
                ],
                correct: 1,
                explanation: 'Close doors to slow smoke. Signal rescuers from a window.',
                points: 15
            },
            {
                id: 'fi_sc7',
                situation: 'You smell smoke but don’t see flames.',
                question: 'What should you do?',
                options: [
                    'Ignore it — it might be cooking',
                    'Check all rooms and get out if you’re unsure',
                    'Turn on fans to clear the air',
                    'Spray air freshener'
                ],
                correct: 1,
                explanation: 'Smoke means fire. Don’t assume it’s harmless. Evacuate and investigate from outside.',
                points: 15
            },
            {
                id: 'fi_sc8',
                situation: 'You’re using a space heater.',
                question: 'How far should it be from furniture?',
                options: [
                    'Touching is fine',
                    'At least 1 foot away',
                    'At least 3 feet away',
                    'Under the sofa'
                ],
                correct: 2,
                explanation: 'Heaters need 3 feet of clearance to prevent ignition.',
                points: 15
            },
            {
                id: 'fi_sc9',
                situation: 'You hear a fire alarm at work.',
                question: 'What should you do?',
                options: [
                    'Finish your task first',
                    'Evacuate immediately using the nearest exit',
                    'Check if it’s a drill',
                    'Call your manager'
                ],
                correct: 1,
                explanation: 'Always treat alarms as real. Evacuate immediately.',
                points: 15
            },
            {
                id: 'fi_sc10',
                situation: 'Your smoke detector beeps once per minute.',
                question: 'What does this mean?',
                options: [
                    'Fire detected',
                    'Low battery — replace it',
                    'It’s working normally',
                    'Needs cleaning'
                ],
                correct: 1,
                explanation: 'A chirp every minute means replace the battery soon.',
                points: 15
            }
        ],
        assessment: [
            {
                question: 'If your clothes catch fire, you should:',
                options: [
                    'Run for help',
                    'Stop, Drop, and Roll',
                    'Pour water on yourself first',
                    'Take off your clothes while running'
                ],
                correct: 1,
                points: 10
            },
            {
                question: 'How should you escape through smoke?',
                options: [
                    'Stand up and run',
                    'Crawl low under the smoke',
                    'Cover yourself with a blanket and walk',
                    'Wait for the smoke to clear'
                ],
                correct: 1,
                points: 10
            },
            {
                question: 'How often should you test smoke detectors?',
                options: [
                    'Never',
                    'Once a year',
                    'Every month',
                    'Only when the battery is low'
                ],
                correct: 2,
                points: 10
            },
            {
                question: 'Your escape route is blocked by fire. You should:',
                options: [
                    'Try to run through it',
                    'Use an alternate escape route',
                    'Hide in a closet',
                    'Break windows and jump'
                ],
                correct: 1,
                points: 10
            },
            {
                question: 'When using a fire extinguisher, remember PASS. What does it stand for?',
                options: [
                    'Point, Aim, Spray, Sweep',
                    'Pull, Aim, Squeeze, Sweep',
                    'Push, Activate, Spray, Stop',
                    'Prepare, Aim, Shoot, Save'
                ],
                correct: 1,
                points: 10
            },
            {
                question: 'A grease fire starts in your kitchen. You should:',
                options: [
                    'Pour water on it',
                    'Cover it with a lid and turn off the heat',
                    'Carry the pan outside',
                    'Use a wet towel to fan it out'
                ],
                correct: 1,
                points: 10
            },
            {
                question: 'After escaping a fire, you should:',
                options: [
                    'Go back for your belongings',
                    'Stay outside and call 911',
                    'Try to put out the fire yourself',
                    'Go back to help others'
                ],
                correct: 1,
                points: 10
            },
            {
                question: 'How many working smoke alarms should you have?',
                options: [
                    'One per house',
                    'One on each level and in each bedroom',
                    'Only in the kitchen',
                    'None if you have a fire extinguisher'
                ],
                correct: 1,
                points: 10
            }
        ]
    },
    typhoon: {
        title: '🌀 Typhoon Preparedness Drill',
        learning: {
            content: `
                <h6>Typhoon Signals</h6>
                <ul>
                    <li><strong>Signal #1:</strong> 30-60 kph - Be alert</li>
                    <li><strong>Signal #2:</strong> 61-120 kph - Prepare</li>
                    <li><strong>Signal #3:</strong> 121-170 kph - Very dangerous</li>
                    <li><strong>Signal #4:</strong> 171-220 kph - Extremely dangerous</li>
                    <li><strong>Signal #5:</strong> 220+ kph - Super typhoon</li>
                </ul>
                <h6>The Eye</h6>
                <p><strong>WARNING:</strong> The eye brings calm but winds will return from opposite direction, often stronger!</p>
            `
        },
        scenarios: [
            {
                id: 'ty_sc1',
                situation: 'Signal #3 is raised. Strong winds and rain. Suddenly everything goes calm and sunny.',
                question: 'What does this mean and what should you do?',
                options: [
                    'Typhoon is over, safe to go outside now',
                    'You are in the eye of the typhoon, stay inside - more winds coming',
                    'Typhoon has weakened significantly',
                    'Good time to check for damage outside'
                ],
                correct: 1,
                explanation: 'The eye is temporary and dangerous. Winds will resume from the opposite direction.',
                points: 15
            },
            {
                id: 'ty_sc2',
                situation: 'Signal #2 announced. Typhoon will arrive tomorrow morning. You have limited supplies.',
                question: 'What should you prioritize NOW?',
                options: [
                    'Nothing, wait for Signal #3',
                    'Secure outdoor items, stock supplies, charge devices',
                    'Go to the mall to shop',
                    'Wait until the typhoon starts'
                ],
                correct: 1,
                explanation: 'Signal #2 is your preparation window. Secure your home NOW.',
                points: 15
            },
            {
                id: 'ty_sc3',
                situation: 'During typhoon, power goes out. You need light.',
                question: 'What is the safest light source?',
                options: [
                    'Light candles throughout the house',
                    'Use flashlights or battery-powered lamps',
                    'Use gas lamp',
                    'Open curtains for natural light'
                ],
                correct: 1,
                explanation: 'Candles and gas lamps are fire hazards during high winds.',
                points: 15
            },
            {
                id: 'ty_sc4',
                situation: 'Strong winds break your window. Rain is coming in.',
                question: 'What should you do?',
                options: [
                    'Tape the window with X-pattern tape',
                    'Board it up if safe, otherwise move to an interior room',
                    'Open other windows to equalize pressure',
                    'Stand near it to monitor'
                ],
                correct: 1,
                explanation: 'Stay away from broken glass. Move to a windowless interior room.',
                points: 15
            },
            {
                id: 'ty_sc5',
                situation: 'Your area is under Signal #4. Evacuation center is 2km away.',
                question: 'Should you walk there now?',
                options: [
                    'Yes, leave immediately',
                    'No, it’s too dangerous — shelter in place in a safe room',
                    'Only if it stops raining',
                    'Drive there quickly'
                ],
                correct: 1,
                explanation: 'At Signal #4, winds are extreme. Walking or driving is life-threatening. Shelter in place.',
                points: 15
            },
            {
                id: 'ty_sc6',
                situation: 'You hear a loud roaring sound during the typhoon.',
                question: 'What might this indicate?',
                options: [
                    'Heavy rain',
                    'Possible tornado or flying debris',
                    'Wind in the trees',
                    'Power lines swinging'
                ],
                correct: 1,
                explanation: 'A loud roar can signal a tornado or structural failure. Take cover immediately.',
                points: 15
            },
            {
                id: 'ty_sc7',
                situation: 'Water is rising in your neighborhood during the typhoon.',
                question: 'What should you do?',
                options: [
                    'Stay put — it will recede soon',
                    'Move to upper floors or roof if safe',
                    'Wade through to check on neighbors',
                    'Start pumping water out'
                ],
                correct: 1,
                explanation: 'Flood water during typhoons rises quickly and is dangerous. Go up, don’t go out.',
                points: 15
            },
            {
                id: 'ty_sc8',
                situation: 'After the typhoon, you see a downed power line.',
                question: 'What should you do?',
                options: [
                    'Move it with a stick',
                    'Assume it’s live and stay far away',
                    'Call the utility and stand nearby to warn others',
                    'Take a photo'
                ],
                correct: 1,
                explanation: 'Downed lines can be energized even if they’re not sparking. Stay far away.',
                points: 15
            },
            {
                id: 'ty_sc9',
                situation: 'You’re preparing your typhoon kit.',
                question: 'Which item is MOST important?',
                options: [
                    'Extra clothes',
                    '3-day supply of water (1 gallon per person per day)',
                    'Books for entertainment',
                    'Extra shoes'
                ],
                correct: 1,
                explanation: 'Water is critical. You can survive weeks without food, but only days without water.',
                points: 15
            },
            {
                id: 'ty_sc10',
                situation: 'Your roof starts leaking during the typhoon.',
                question: 'What should you do?',
                options: [
                    'Go outside to fix it',
                    'Place buckets inside and stay away from electrical outlets',
                    'Turn on all lights to see better',
                    'Ignore it until it stops'
                ],
                correct: 1,
                explanation: 'Never go outside during a typhoon. Manage leaks indoors safely.',
                points: 15
            }
        ],
        assessment: [
            {
                question: 'What is Signal #3?',
                options: [
                    'Light winds (30-60 kph)',
                    'Moderate winds (61-120 kph)',
                    'Strong winds (121-170 kph)',
                    'Extreme winds (220+ kph)'
                ],
                correct: 2,
                points: 10
            },
            {
                question: 'During the "eye" of a typhoon you should:',
                options: [
                    'Go outside to check damage',
                    'Stay indoors - more winds are coming',
                    'Evacuate immediately',
                    'Start cleaning up'
                ],
                correct: 1,
                points: 10
            },
            {
                question: 'When should you prepare for a typhoon?',
                options: [
                    'When it starts raining',
                    'At Signal #3 or higher',
                    'At Signal #1 or #2',
                    'After the typhoon passes'
                ],
                correct: 2,
                points: 10
            },
            {
                question: 'What should you do with outdoor items when typhoon is coming?',
                options: [
                    'Leave them as they are',
                    'Bring them inside or secure them',
                    'Just cover them with plastic',
                    'Move them to the garage only'
                ],
                correct: 1,
                points: 10
            },
            {
                question: 'How much water should you store per person per day?',
                options: [
                    '1 cup',
                    '1 liter',
                    '3-4 liters (1 gallon)',
                    '10 liters'
                ],
                correct: 2,
                points: 10
            },
            {
                question: 'Where is the safest room during a typhoon?',
                options: [
                    'Room with large windows',
                    'Interior room away from windows',
                    'Bathroom with window',
                    'Any room is fine'
                ],
                correct: 1,
                points: 10
            },
            {
                question: 'After a typhoon, you should:',
                options: [
                    'Immediately use all electrical appliances',
                    'Check for damage, avoid flood water, listen for updates',
                    'Go sightseeing to see the damage',
                    'Swim in flood water'
                ],
                correct: 1,
                points: 10
            },
            {
                question: 'What items should be in your typhoon emergency kit?',
                options: [
                    'Only food',
                    'Water, food, flashlight, radio, first aid, important documents',
                    'Just a radio',
                    'Only clothes'
                ],
                correct: 1,
                points: 10
            }
        ]
    }
};

// Load drill
function loadDrill(drillType) {
    console.log('Loading drill:', drillType);
    const drill = drillData[drillType];
    if (!drill) {
        console.error('Drill not found:', drillType);
        alert('Drill data not found!');
        return;
    }
    document.getElementById('drillTitle').textContent = drill.title;
    document.getElementById('drillContent').style.display = 'block';
    checkDrillStatus();
    document.getElementById('drillContent').scrollIntoView({ behavior: 'smooth' });
}

// Check if drill already completed
async function checkDrillStatus() {
    try {
        const response = await fetch(`../api/drill/get-progress.php?drill_type=${currentDrill}`);
        const data = await response.json();
        if (data.success && data.data) {
            const progress = data.data;
            const quizResults = JSON.parse(progress.quiz_results || '[]');
            if (quizResults.length > 0) {
                console.log('Drill already completed');
                currentPhase = 'completed';
                showCompletionPhase(progress);
            } else {
                console.log('Starting new drill');
                currentPhase = 'learning';
                showLearningPhase();
            }
        } else {
            console.log('No progress found, starting fresh');
            showLearningPhase();
        }
    } catch (error) {
        console.error('Error checking status:', error);
        showLearningPhase();
    }
}

// Show Learning Phase
function showLearningPhase() {
    console.log('Showing learning phase');
    hideAllPhases();
    document.getElementById('learningPhase').style.display = 'block';
    document.getElementById('phase1Badge').classList.remove('bg-secondary');
    document.getElementById('phase1Badge').classList.add('bg-primary');
    const drill = drillData[currentDrill];
    document.getElementById('learningContent').innerHTML = drill.learning.content;
    updateProgress(25, 'Phase 1: Reading learning materials');
}

// Complete phase
function completePhase(phase) {
    console.log('Completing phase:', phase);
    if (phase === 'learning') {
        currentPhase = 'practice';
        showNextPracticeScenario();
    }
}

// Show next practice scenario (1 at a time)
function showNextPracticeScenario() {
    console.log('Showing next practice scenario');
    hideAllPhases();
    document.getElementById('practicePhase').style.display = 'block';
    document.getElementById('phase1Badge').classList.remove('bg-primary');
    document.getElementById('phase1Badge').classList.add('bg-success');
    document.getElementById('phase2Badge').classList.remove('bg-secondary');
    document.getElementById('phase2Badge').classList.add('bg-primary');
    
    const drill = drillData[currentDrill];
    const pool = drill.scenarios;
    
    // Find unused scenarios
    const unusedIndices = [];
    for (let i = 0; i < pool.length; i++) {
        if (!practiceUsedIndices.includes(i)) {
            unusedIndices.push(i);
        }
    }
    
    if (unusedIndices.length === 0) {
        // All scenarios completed
        setTimeout(() => {
            if (confirm('Practice complete! Take final assessment?')) {
                showAssessmentPhase();
            }
        }, 1000);
        return;
    }
    
    // Pick random unused index
    const randomIndex = unusedIndices[Math.floor(Math.random() * unusedIndices.length)];
    currentPracticeIndex = randomIndex;
    practiceUsedIndices.push(randomIndex);
    
    // Render single scenario
    const scenario = pool[randomIndex];
    document.getElementById('scenarioContent').innerHTML = `
        <div class="card">
            <div class="card-body">
                <h6>Scenario ${practiceUsedIndices.length} of ${pool.length}</h6>
                <p class="text-muted">${scenario.situation}</p>
                <p><strong>${scenario.question}</strong></p>
                ${scenario.options.map((opt, i) => `
                    <div class="form-check">
                        <input class="form-check-input scenario-radio" type="radio" 
                               name="practice_scenario" value="${i}" id="ps_${i}">
                        <label class="form-check-label" for="ps_${i}">${opt}</label>
                    </div>
                `).join('')}
                <button class="btn btn-primary btn-sm mt-2" 
                        onclick="checkPracticeScenario(${scenario.correct}, \`${scenario.explanation}\`, ${scenario.points})">
                    Check Answer
                </button>
                <div id="practiceFeedback" class="mt-2"></div>
            </div>
        </div>
    `;
    updateProgress(50, `Phase 2: Scenario ${practiceUsedIndices.length}/${pool.length}`);
}

// Check practice scenario answer
async function checkPracticeScenario(correct, explanation, points) {
    console.log('Checking practice scenario');
    const selected = document.querySelector('input[name="practice_scenario"]:checked');
    if (!selected) {
        alert('Please select an answer');
        return;
    }
    const userAnswer = parseInt(selected.value);
    const feedback = document.getElementById('practiceFeedback');
    if (userAnswer === correct) {
        feedback.innerHTML = `<div class="alert alert-success">✅ Correct! +${points} pts</div>`;
        await awardPoints(`practice_${currentPracticeIndex}`, points);
        // Show next after delay
        setTimeout(() => {
            showNextPracticeScenario();
        }, 1500);
    } else {
        // Only show HINT — not the correct answer
        feedback.innerHTML = `
            <div class="alert alert-warning">
                ❌ Not correct. Try again!<br>
                <small><strong>Hint:</strong> ${explanation}</small>
            </div>
        `;
        // Do NOT disable — allow retry
    }
}

// Award points
async function awardPoints(taskId, points) {
    try {
        const response = await fetch('../api/drill/complete-task.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                drill_type: currentDrill,
                task_id: taskId,
                task_name: 'Scenario',
                points_earned: points
            })
        });
        const data = await response.json();
        if (data.success) {
            totalPointsEarned += points;
            updateTotalPoints();
        }
    } catch (error) {
        console.error('Error awarding points:', error);
    }
}

// Show Assessment Phase
function showAssessmentPhase() {
    console.log('Showing assessment phase');
    hideAllPhases();
    document.getElementById('assessmentPhase').style.display = 'block';
    document.getElementById('phase2Badge').classList.remove('bg-primary');
    document.getElementById('phase2Badge').classList.add('bg-success');
    document.getElementById('phase3Badge').classList.remove('bg-secondary');
    document.getElementById('phase3Badge').classList.add('bg-warning');
    const drill = drillData[currentDrill];
    loadAssessment(drill.assessment);
    startAssessmentTimer();
    updateProgress(75, 'Phase 3: Final assessment');
}

// Start assessment timer
function startAssessmentTimer() {
    assessmentStartTime = Date.now();
    timeRemainingSeconds = 600;
    updateTimerDisplay();
    assessmentTimer = setInterval(() => {
        timeRemainingSeconds--;
        updateTimerDisplay();
        if (timeRemainingSeconds <= 0) {
            clearInterval(assessmentTimer);
            autoSubmitAssessment();
        }
        if (timeRemainingSeconds === 120) {
            playWarningSound();
            showTimerWarning('⚠️ Only 2 minutes remaining!');
        }
        if (timeRemainingSeconds === 60) {
            showTimerWarning('⚠️ Only 1 minute remaining!');
        }
    }, 1000);
}

function updateTimerDisplay() {
    const minutes = Math.floor(timeRemainingSeconds / 60);
    const seconds = timeRemainingSeconds % 60;
    const display = `${minutes}:${seconds.toString().padStart(2, '0')}`;
    document.getElementById('timerDisplay').textContent = display;
    const percentRemaining = (timeRemainingSeconds / 600) * 100;
    const progressBar = document.getElementById('timerProgress');
    progressBar.style.width = percentRemaining + '%';
    const timerAlert = document.getElementById('timerAlert');
    if (timeRemainingSeconds <= 60) {
        timerAlert.className = 'alert alert-danger mb-4';
        progressBar.className = 'progress-bar bg-danger';
    } else if (timeRemainingSeconds <= 120) {
        timerAlert.className = 'alert alert-warning mb-4';
        progressBar.className = 'progress-bar bg-warning';
    } else if (timeRemainingSeconds <= 300) {
        timerAlert.className = 'alert alert-info mb-4';
        progressBar.className = 'progress-bar bg-info';
    } else {
        timerAlert.className = 'alert alert-warning mb-4';
        progressBar.className = 'progress-bar bg-success';
    }
}

function showTimerWarning(message) {
    const toast = `
        <div class="toast align-items-center text-white bg-danger border-0 show" 
             style="position: fixed; top: 80px; right: 20px; z-index: 9999;" role="alert">
            <div class="d-flex">
                <div class="toast-body"><strong>${message}</strong></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" 
                        onclick="this.parentElement.parentElement.remove()"></button>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', toast);
    setTimeout(() => {
        document.querySelector('.toast')?.remove();
    }, 5000);
}

function playWarningSound() {
    try {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();
        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);
        oscillator.frequency.value = 800;
        oscillator.type = 'sine';
        gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
        oscillator.start(audioContext.currentTime);
        oscillator.stop(audioContext.currentTime + 0.5);
    } catch (e) {
        console.log('Audio not supported');
    }
}

function autoSubmitAssessment() {
    alert('⏰ Time expired! Assessment will be submitted automatically.');
    submitAssessment(true);
}

function loadAssessment(questions) {
    const container = document.getElementById('assessmentContent');
    container.innerHTML = '';
    questions.forEach((q, i) => {
        const html = `
            <div class="mb-4">
                <p><strong>Q${i+1}:</strong> ${q.question}</p>
                ${q.options.map((opt, j) => `
                    <div class="form-check">
                        <input class="form-check-input" type="radio" 
                               name="as_${i}" value="${j}" id="as_${i}_${j}">
                        <label class="form-check-label" for="as_${i}_${j}">${opt}</label>
                    </div>
                `).join('')}
            </div>
        `;
        container.innerHTML += html;
    });
}

// Submit assessment
async function submitAssessment(autoSubmit = false) {
    console.log('Submitting assessment');
    if (assessmentTimer) {
        clearInterval(assessmentTimer);
    }
    const drill = drillData[currentDrill];
    const questions = drill.assessment;
    let score = 0;
    let allAnswered = true;
    questions.forEach((q, i) => {
        const selected = document.querySelector(`input[name="as_${i}"]:checked`);
        if (!selected) {
            allAnswered = false;
        } else if (parseInt(selected.value) === q.correct) {
            score++;
        }
    });
    if (!allAnswered && !autoSubmit) {
        alert('Please answer all questions before submitting');
        startAssessmentTimer();
        return;
    }
    const passingScore = Math.ceil(questions.length * 0.6);
    const passed = score >= passingScore;
    const timeTaken = Math.floor((Date.now() - assessmentStartTime) / 1000);
    const minutesTaken = Math.floor(timeTaken / 60);
    const secondsTaken = timeTaken % 60;
    if (!autoSubmit) {
        const confirmMsg = `Submit assessment?
Your Score: ${score}/${questions.length} (${Math.round((score/questions.length)*100)}%)
Passing Score: ${passingScore}/${questions.length} (60%)
Time: ${minutesTaken}:${secondsTaken.toString().padStart(2, '0')}
${passed ? '✅ You PASSED!' : '❌ You FAILED - Need 60% to pass'}
This can only be done ONCE!`;
        if (!confirm(confirmMsg)) {
            startAssessmentTimer();
            return;
        }
    }
    let earnedPoints;
    if (passed) {
        const percentCorrect = score / questions.length;
        earnedPoints = Math.round(30 + (percentCorrect - 0.6) * 50);
    } else {
        earnedPoints = 10;
    }
    try {
        const response = await fetch('../api/drill/submit-quiz.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                drill_type: currentDrill,
                quiz_id: `${currentDrill}_assessment`,
                score: score,
                total_questions: questions.length,
                time_taken: timeTaken
            })
        });
        const data = await response.json();
        if (data.success) {
            totalPointsEarned += earnedPoints;
            // NEW: Sync total points to user profile
            await fetch('../api/user/update-points.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ points: totalPointsEarned })
            });
            showCompletionPhase({ 
                total_points: totalPointsEarned,
                assessment_points: earnedPoints,
                score, 
                total_questions: questions.length,
                time_taken: `${minutesTaken}:${secondsTaken.toString().padStart(2, '0')}`,
                passed: passed
            });
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to submit assessment. Please try again.');
        startAssessmentTimer();
    }
}

// Show completion
function showCompletionPhase(progress) {
    if (assessmentTimer) {
        clearInterval(assessmentTimer);
    }
    hideAllPhases();
    document.getElementById('completionPhase').style.display = 'block';
    document.getElementById('phase3Badge').classList.remove('bg-warning');
    document.getElementById('phase3Badge').classList.add('bg-success');
    document.getElementById('phase4Badge').classList.remove('bg-secondary');
    document.getElementById('phase4Badge').classList.add('bg-success');
    document.getElementById('completedDrillName').textContent = drillData[currentDrill].title;
    document.getElementById('totalPointsEarned').textContent = progress.total_points || totalPointsEarned;
    
    // Clear progress details and show breakdown
    let breakdown = `
        <p><strong>Practice Points:</strong> ${progress.total_points - progress.assessment_points} pts</p>
        <p><strong>Assessment Points:</strong> ${progress.assessment_points} pts</p>
    `;
    if (progress.time_taken) {
        breakdown += `<p><strong>Time Taken:</strong> ${progress.time_taken}</p>`;
    }
    document.getElementById('progressDetails').innerHTML = breakdown;
    
    updateProgress(100, 'Drill completed!');
    loadBadges();
}

// Helper functions
function hideAllPhases() {
    document.getElementById('learningPhase').style.display = 'none';
    document.getElementById('practicePhase').style.display = 'none';
    document.getElementById('assessmentPhase').style.display = 'none';
    document.getElementById('completionPhase').style.display = 'none';
}

function updateProgress(percent, message) {
    const bar = document.getElementById('progressBar');
    bar.style.width = percent + '%';
    bar.textContent = percent + '%';
    document.getElementById('progressDetails').innerHTML = `
        <p><strong>Status:</strong> ${message}</p>
        <p><strong>Points:</strong> ${totalPointsEarned}</p>
    `;
}

function updateTotalPoints() {
    const current = parseInt(document.getElementById('totalPoints').textContent);
    document.getElementById('totalPoints').textContent = current + totalPointsEarned;
}

async function loadBadges() {
    try {
        const response = await fetch('../api/drill/get-badges.php');
        const data = await response.json();
        if (data.success && data.data.length > 0) {
            const html = data.data.slice(-3).map(b => `
                <div class="badge bg-warning text-dark m-1 p-2">🏆 ${b.name}</div>
            `).join('');
            document.getElementById('badgesEarned').innerHTML = html;
        }
    } catch (error) {
        console.error('Error loading badges:', error);
    }
}

// Badges modal
document.getElementById('badgesModal')?.addEventListener('show.bs.modal', async function() {
    try {
        const response = await fetch('../api/drill/get-badges.php');
        const data = await response.json();
        const content = document.getElementById('badgesContent');
        if (data.success && data.data.length > 0) {
            content.innerHTML = '<div class="row">' + data.data.map(b => `
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h1>🏆</h1>
                            <h5>${b.name}</h5>
                            <p class="text-muted">${b.description}</p>
                            <small>${new Date(b.earned_at).toLocaleDateString()}</small>
                        </div>
                    </div>
                </div>
            `).join('') + '</div>';
        } else {
            content.innerHTML = '<p class="text-center">No badges yet. Complete drills to earn badges!</p>';
        }
    } catch (error) {
        console.error('Error:', error);
    }
});

console.log('Drill.js initialization complete!');