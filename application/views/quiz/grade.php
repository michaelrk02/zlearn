<h1>Quiz Grading</h1>
<p><b>Course:</b> <a href="<?php echo site_url('course/view').'?id='.urlencode($quiz['course_id']); ?>"><?php echo htmlspecialchars($course['title']); ?></a></p>
<p><b>Quiz:</b> <a href="<?php echo site_url('quiz/view').'?id='.urlencode($id); ?>"><?php echo htmlspecialchars($quiz['title']); ?></a> - <a href="<?php echo site_url('quiz/viewpdf').'?id='.urlencode($id); ?>" target="_blank">View PDF</a></p>
<p><b>Respondent:</b> <?php echo $user['name']; ?> <span class="text-muted">[<?php echo $user_id; ?>]</span></p>
<hr>
<h3><?php echo $user['name']; ?>'s Answer</h3>
<p>Question number: <b><?php echo $question_no; ?></b></p>
<div><pre><?php echo htmlspecialchars($response['data'][0]); ?></pre></div>
<hr>
