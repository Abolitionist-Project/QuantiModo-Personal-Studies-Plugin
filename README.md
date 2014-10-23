QM-Personal-Studies-Plugin
==========================


A WordPress plugin to allow citizen scientists to share their discoveries. This plugin could be used to automatically create studies using the "Study" custom post by selecting  one or more variables. 

The study post would display:
- Title: Question to be answered by the study.
- Conclusion: Answer the question through interpretation of results. 
- Hypothetical Cause:
- Hypothetical Effect: 
- Principal Investigator: Person who created the study.
- Background: Reason for the study and/or existing research on topic. 

Results: 
- Scatterplot with hypothetical effect on y axis and hypothetical cause on x-axis; 
- Parameters of analysis (variable settings); 
- Data download option;  
- Timeline graph (optional)
- Methods: List of apps and devices used to obtain the measurements.
- Limitations (Optional): Include with any reservations like potentially confouding variables, sampling error, etc
- Suggestions for further research. 


Walkthrough of User Actions:

- Creates a Study custom post.
- Enters a research question like "What affects my mood?" in the title of the post
- Enters some text description of the study in the post body content
- Selects an "Examined Variable"
- Selects "What is predictive of EXAMINED VARIABLE?" or "What does EXAMINED VARIABLE predict?"
- User presses publish.
- Study posts are listed on a WP Page Called Studies
- User click on their study title and excerpt in the Studies page.
- User is taken to a Study post
- Study post contains the Research Question (Title) at the top
- User study post body content is below the title

I'll outline what I think we need, but I'd be totally grateful for any suggestions you have on how we could improve it or make implementation easier. It's not always clear to me how difficult each part is, so it's best for you to let me know what the low hanging fruit are and what's more difficult to implement. 

That said, most of the coding for this has been done so the task is primarily a matter of storing the API call parameters in the WP DB. 

We've got this front end WP page template using Highcharts that are fed via our API with requests defined by the variable selector on the left: https://www.dropbox.com/s/jx7r7u3l0hlbgci/Screenshot%202014-10-11%2009.59.35.png?dl=0

You can log in here: https://quantimo.do/correlate/
user: quantimodo
pw: B1ggerstaff!

So instead of logging in and selecting variables on the front end, I want to define all the parameters on the back end and then publish a post with specific variables selected.  We should also be able to specify the units, time range, and a couple other parameters. 

A basic mockup of our goal for the "Studies" custom post type plugin is here: http://app.mockflow.com/view/07C883E489899C30BE88FD1DECDDD4C9  Again, I'd love any suggestions on how that might be improved or could be more easily implemented. 

So here's what still needs to be done:
- The plugin needs to be cleaned up to match the mockup. 
- The variable selectors in the back end should be populated using our quantimo.do/variables/search endpoint (it's an auto-complete search that is illustrated in this plugin: https://chrome.google.com/webstore/detail/quantimodo-beta/jioloifallegdkgjklafkkbniianjbgi)
- The rest of the selectors should be populated with the same JavaScript calls to the API that we currently use on the front end
- Then when the user publishes a "Study", it should create a new post like this: https://quantimo.do/deep-sleep-is-strongest-predictor-of-short-term-mood-in-subject-with-inflammation-mediated-depression/ The part at the top is the excerpt. In the final product, that should be followed by either a dynamic version of the scatterplot and timeline just like the front end. The difference between the current dashboard is that the variable selection was already done in the back and and is static.  The parameters for the API call would them be taken from those custom fields in the WP post meta table, I guess.
- We should enable that Highcharts image download thing they have in the top righthand corner. 
- Add the following shortcode or equivalent PHP to produce a list of apps that track the variable category for the cause and effect at the bottom.  For instance, the variable "Overall Mood" is in the "Mood" category, so I've included this shortcode [portfolio load="20" columns="5" orderby="menu_order" showtitle="yes" showdate="no" category="mood"] at the bottom of this page: https://quantimo.do/deep-sleep-is-strongest-predictor-of-short-term-mood-in-subject-with-inflammation-mediated-depression/ 
